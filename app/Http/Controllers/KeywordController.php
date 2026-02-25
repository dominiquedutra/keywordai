<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKeywordRequest;
use App\Jobs\AddKeywordToAdGroupJob;
use App\Models\AdGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class KeywordController extends Controller
{
    /**
     * Show the form for adding a new keyword.
     *
     * @param Request $request
     * @return View
     */
    public function create(Request $request): View
    {
        // Get data from query parameters passed by the notification link
        $searchTerm = $request->query('term', '');
        $adGroupId = $request->query('ad_group_id', '');
        $adGroupName = $request->query('ad_group_name', 'N/A'); // Get Ad Group name for display

        // Basic validation for required parameters from URL
        if (empty($searchTerm) || empty($adGroupId) || !is_numeric($adGroupId)) {
            Log::warning('KeywordController@create: Missing or invalid parameters in URL.', $request->query());
            // Optionally, return an error view or redirect with an error message
            // For now, we'll let the view handle potentially empty values,
            // but the form submission will fail validation later if they are missing.
        }

        // Buscar todos os grupos de anúncios ativos com suas campanhas ativas do tipo SEARCH
        $adGroups = AdGroup::with('campaign')
            ->where('status', 'ENABLED') // Garante que o Grupo de Anúncios esteja ativo
            ->whereHas('campaign', function ($query) { // Adiciona condição na Campanha relacionada
                $query->where('status', 'ENABLED') // Garante que a Campanha também esteja ativa
                      ->where('advertising_channel_type', 'SEARCH'); // Garante que a Campanha seja do tipo SEARCH
            })
            ->orderBy('name')
            ->get()
            ->map(function ($adGroup) {
                return [
                    'id' => $adGroup->id,
                    'text' => $adGroup->formatted_name,
                    'google_ad_group_id' => $adGroup->google_ad_group_id,
                ];
            });

        Log::info("Exibindo formulário para adicionar palavra-chave: '{$searchTerm}'", [
            'adGroupId' => $adGroupId,
            'adGroupName' => $adGroupName,
            'userId' => Auth::id(),
        ]);

        return view('keyword.create', [
            'search_term' => $searchTerm,
            'ad_group_id' => $adGroupId,
            'ad_group_name' => $adGroupName,
            'default_match_type' => \App\Models\Setting::getValue('default_keyword_match_type', 'phrase'), // Usar configuração do banco
            'ad_groups' => $adGroups, // Passar a lista de grupos de anúncios para a view
        ]);
    }

    /**
     * Store a newly created keyword by dispatching a job.
     *
     * @param StoreKeywordRequest $request
     * @return RedirectResponse
     */
    public function store(StoreKeywordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Obter o ID do usuário autenticado
        $userId = Auth::id();

        // Get Client Customer ID from config
        $clientCustomerId = config('app.client_customer_id');
        if (empty($clientCustomerId)) {
            $iniPath = config('app.google_ads_php_path');
            if (file_exists($iniPath)) {
                $iniConfig = parse_ini_file($iniPath, true);
                $clientCustomerId = $iniConfig['GOOGLE_ADS']['clientCustomerId'] ?? null;
            }
        }
        if (empty($clientCustomerId)) {
            Log::error('KeywordController@store: Client Customer ID is missing in configuration.', [
                'userId' => $userId,
            ]);
            // Redirect back with an error message
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erro de configuração interna. Não foi possível adicionar a palavra-chave.');
        }

        try {
            Log::info("Dispatching AddKeywordToAdGroupJob from KeywordController.", array_merge($validated, ['userId' => $userId]));

            // Buscar o grupo de anúncios no banco de dados para obter o ID do Google Ads
            $adGroup = \App\Models\AdGroup::find($validated['ad_group_id']);
            
            if (!$adGroup) {
                Log::error('KeywordController@store: Grupo de anúncios não encontrado.', [
                    'ad_group_id' => $validated['ad_group_id'],
                    'userId' => $userId,
                ]);
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Grupo de anúncios não encontrado. Por favor, tente novamente.');
            }
            
            AddKeywordToAdGroupJob::dispatch(
                $validated['search_term'],
                (int)$adGroup->google_ad_group_id, // Usar o ID do Google Ads em vez do ID do banco de dados
                $validated['match_type'], // Already validated as 'exact', 'phrase', or 'broad'
                $clientCustomerId,
                $userId // Passar o ID do usuário autenticado
            )->onQueue('critical');

            Log::info("Job AddKeywordToAdGroupJob despachado com sucesso para o termo: '{$validated['search_term']}'", [
                'userId' => $userId,
                'adGroupId' => $adGroup->google_ad_group_id,
                'matchType' => $validated['match_type'],
            ]);

            // Redirect to a success page
            return redirect()->route('keyword.add.success');

        } catch (\Exception $e) {
            Log::error("Error dispatching AddKeywordToAdGroupJob from KeywordController:", [
                'error' => $e->getMessage(),
                'data' => $validated,
                'userId' => $userId,
            ]);
            // Redirect back with an error message
            return redirect()->back()
                ->withInput()
                ->with('error', 'Ocorreu um erro ao tentar adicionar a palavra-chave. Tente novamente.');
        }
    }

    /**
     * Show the success page after dispatching the job.
     *
     * @return View
     */
    public function success(): View
    {
        return view('keyword.success');
    }
}
