<?php

namespace App\Http\Controllers\Api;

use App\Models\Campaign;
use App\Models\NegativeKeyword;
use App\Models\SearchTerm;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends BaseApiController
{
    /**
     * Obter métricas gerais do dashboard.
     */
    public function metrics(): JsonResponse
    {
        $today = Carbon::today();
        $weekAgo = Carbon::today()->subDays(7);
        $monthAgo = Carbon::today()->subDays(30);

        $metrics = [
            'search_terms' => [
                'total' => SearchTerm::count(),
                'today' => SearchTerm::whereDate('created_at', $today)->count(),
                'this_week' => SearchTerm::whereDate('created_at', '>=', $weekAgo)->count(),
                'this_month' => SearchTerm::whereDate('created_at', '>=', $monthAgo)->count(),
            ],
            'campaigns' => [
                'total' => Campaign::count(),
                'active' => Campaign::active()->count(),
            ],
            'negative_keywords' => [
                'total' => NegativeKeyword::count(),
                'this_week' => NegativeKeyword::where('created_at', '>=', $weekAgo)->count(),
            ],
            'performance' => [
                'total_impressions' => SearchTerm::sum('impressions'),
                'total_clicks' => SearchTerm::sum('clicks'),
                'total_cost_micros' => SearchTerm::sum('cost_micros'),
                'avg_ctr' => SearchTerm::avg('ctr'),
            ],
            'by_status' => SearchTerm::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
        ];

        return $this->successResponse($metrics);
    }

    /**
     * Dados para gráfico de novos termos.
     */
    public function newTermsChart(Request $request): JsonResponse
    {
        $days = $request->input('days', 30);
        $days = min(max((int) $days, 7), 90);

        $startDate = Carbon::today()->subDays($days);

        $data = SearchTerm::selectRaw('DATE(first_seen_at) as date, COUNT(*) as count')
            ->whereDate('first_seen_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Preencher datas vazias
        $chartData = [];
        $currentDate = $startDate->copy();
        $dataByDate = $data->keyBy('date');

        while ($currentDate <= Carbon::today()) {
            $dateStr = $currentDate->format('Y-m-d');
            $chartData[] = [
                'date' => $dateStr,
                'count' => $dataByDate[$dateStr]->count ?? 0,
            ];
            $currentDate->addDay();
        }

        return $this->successResponse([
            'labels' => collect($chartData)->pluck('date'),
            'data' => collect($chartData)->pluck('count'),
            'period' => "Últimos {$days} dias",
        ]);
    }

    /**
     * Top termos de pesquisa.
     */
    public function topSearchTerms(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $limit = min(max((int) $limit, 1), 100);

        $sortBy = $request->input('sort_by', 'impressions');
        $allowedSorts = ['impressions', 'clicks', 'cost_micros', 'ctr'];

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'impressions';
        }

        $terms = SearchTerm::orderByDesc($sortBy)
            ->limit($limit)
            ->get(['id', 'search_term', 'campaign_name', 'ad_group_name', 'impressions', 'clicks', 'cost_micros', 'ctr']);

        return $this->successResponse($terms);
    }

    /**
     * Atividade recente.
     */
    public function recentActivity(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 20);
        $limit = min(max((int) $limit, 1), 100);

        // Termos recentes
        $recentTerms = SearchTerm::orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'search_term', 'campaign_name', 'created_at'])
            ->map(function ($term) {
                return [
                    'type' => 'new_search_term',
                    'description' => "Novo termo: {$term->search_term}",
                    'campaign' => $term->campaign_name,
                    'created_at' => $term->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Palavras-chave negativas recentes
        $recentNegatives = NegativeKeyword::with('createdBy')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($keyword) {
                return [
                    'type' => 'negative_keyword',
                    'description' => "Palavra-chave negativa: {$keyword->keyword}",
                    'match_type' => $keyword->match_type,
                    'created_by' => $keyword->createdBy?->name ?? 'Sistema',
                    'created_at' => $keyword->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Combinar e ordenar
        $activity = $recentTerms->merge($recentNegatives)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();

        return $this->successResponse($activity);
    }
}
