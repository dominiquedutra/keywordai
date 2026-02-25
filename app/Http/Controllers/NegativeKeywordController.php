<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNegativeKeywordRequest; // Usar o Form Request
use App\Jobs\AddNegativeKeywordJob; // Usar o Job
use App\Models\NegativeKeyword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View; // Para o tipo de retorno de create
use Illuminate\Http\RedirectResponse; // Para o tipo de retorno de store

class NegativeKeywordController extends Controller
{
    /**
     * Display a listing of negative keywords.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        // Get filter parameters from the request
        $keywordFilter = $request->input('keyword');
        $matchTypeFilter = $request->input('match_type');
        $userIdFilter = $request->input('user_id');
        $reasonFilter = $request->input('reason');
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc'); // Default DESC

        // Build the query
        $query = NegativeKeyword::with(['createdBy', 'updatedBy']);

        // Apply filters
        $query->when($keywordFilter, function ($q) use ($keywordFilter) {
            return $q->where('keyword', 'like', '%' . $keywordFilter . '%');
        });

        $query->when($matchTypeFilter, function ($q) use ($matchTypeFilter) {
            return $q->where('match_type', $matchTypeFilter);
        });

        $query->when($userIdFilter, function ($q) use ($userIdFilter) {
            return $q->where('created_by_id', $userIdFilter);
        });

        $query->when($reasonFilter, function ($q) use ($reasonFilter) {
            return $q->where('reason', 'like', '%' . $reasonFilter . '%');
        });

        // Apply dynamic sorting
        $allowedSortColumns = ['created_at', 'keyword', 'match_type', 'created_by_id'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            // Default sort if no valid sort_by is provided
            $query->orderBy('created_at', 'desc');
        }

        // Paginate results
        $negativeKeywords = $query->paginate(50);

        // Get distinct values for filter dropdowns
        $matchTypes = NegativeKeyword::select('match_type')
                                     ->distinct()
                                     ->orderBy('match_type')
                                     ->pluck('match_type');

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        // Return the view with data
        return view('negative_keyword.index', [
            'negativeKeywords' => $negativeKeywords,
            'matchTypes' => $matchTypes,
            'users' => $users,
            'filters' => $request->only(['keyword', 'match_type', 'user_id', 'reason', 'sort_by', 'sort_direction']), // Pass all filters back to view
        ]);
    }

    /**
     * Show the form for creating a new negative keyword.
     *
     * @param Request $request
     * @return View
     */
    public function create(Request $request): View
    {
        // Obter dados da query string (enviados pela notificação do Google Chat)
        $term = $request->query('term');
        $campaignName = $request->query('campaign_name');
        $adGroupName = $request->query('ad_group_name');
        $keywordText = $request->query('keyword_text');
        // Obter o tipo de correspondência da URL, ou usar o valor padrão do banco
        $matchType = $request->query('match_type', \App\Models\Setting::getValue('default_negative_keyword_match_type', 'phrase')); 

        // Validar o matchType recebido da URL (opcional, mas bom para garantir)
        $validMatchTypes = ['broad', 'phrase', 'exact'];
        if (!in_array(strtolower($matchType), $validMatchTypes)) {
            Log::warning("Match type inválido recebido na URL: '{$matchType}'. Usando 'phrase' como padrão.", ['term' => $term]);
            $matchType = 'phrase'; // Garante que seja um valor válido
        }

        // Obter o ID da lista padrão da configuração
        $defaultListId = config('googleads.default_negative_list_id');
        if (!$defaultListId) {
             // Lidar com o caso de ID não configurado - talvez mostrar um erro na view
             Log::error('GOOGLE_ADS_DEFAULT_NEGATIVE_LIST_ID não está configurado em .env ou config/googleads.php');
             // Poderia passar uma variável de erro para a view
        }

        Log::info("Exibindo formulário para negativar termo: '{$term}'", [
            'campaign' => $campaignName,
            'adGroup' => $adGroupName,
            'keyword' => $keywordText,
            'matchType' => $matchType,
            'listId' => $defaultListId,
            'userId' => Auth::id(),
        ]);

        // Passar os dados para a view
        return view('negative_keyword.create', [
            'term' => $term,
            'campaignName' => $campaignName,
            'adGroupName' => $adGroupName,
            'keywordText' => $keywordText,
            'matchType' => $matchType, // Passa o tipo de correspondência (da URL ou padrão)
            'listId' => $defaultListId,
        ]);
    }

    /**
     * Store a newly created negative keyword request in the queue.
     *
     * @param StoreNegativeKeywordRequest $request
     * @return RedirectResponse
     */
    public function store(StoreNegativeKeywordRequest $request): RedirectResponse
    {
        // A validação já foi feita pelo StoreNegativeKeywordRequest
        $validatedData = $request->validated();

        // Obter o ID do usuário autenticado
        $userId = Auth::id();

        Log::info("Recebida requisição para despachar Job de negativação:", array_merge($validatedData, ['userId' => $userId]));

        try {
            // Disparar o Job com os dados validados e o ID do usuário na fila 'critical'
            AddNegativeKeywordJob::dispatch(
                $validatedData['term'],
                $validatedData['list_id'],
                $validatedData['match_type'],
                $validatedData['reason'] ?? null, // Passar a razão (pode ser null)
                $userId // Passar o ID do usuário autenticado
            )->onQueue('critical');

            Log::info("Job AddNegativeKeywordJob despachado com sucesso para o termo: '{$validatedData['term']}'", [
                'userId' => $userId,
                'reason' => $validatedData['reason'] ?? null,
            ]);

            // Redirecionar para a página de sucesso
            return redirect()->route('negative-keyword.success')
                   ->with('status', 'Solicitação para adicionar palavra-chave negativa enviada para processamento!'); // Manter a mensagem flash é opcional, mas pode ser útil

        } catch (\Exception $exception) {
            Log::error('Erro ao despachar AddNegativeKeywordJob do Controller:', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'data' => $validatedData,
                'userId' => $userId,
            ]);

            // Redirecionar de volta com mensagem de erro
            // É importante tratar o erro de forma adequada na UI
            return back()->withInput() // Mantém os dados do formulário
                   ->with('error', 'Ocorreu um erro ao processar sua solicitação. Tente novamente mais tarde.');
        }
    }

    /**
     * Show the success page after submitting the form.
     *
     * @return View
     */
    public function success(): View
    {
        // Simplesmente retorna a view de sucesso
        return view('negative_keyword.success');
    }

    /**
     * Display the specified negative keyword.
     *
     * @param NegativeKeyword $negativeKeyword
     * @return View
     */
    public function show(NegativeKeyword $negativeKeyword): View
    {
        // Load the relationships
        $negativeKeyword->load(['createdBy', 'updatedBy']);

        return view('negative_keyword.show', [
            'negativeKeyword' => $negativeKeyword,
        ]);
    }
}
