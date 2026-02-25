<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AdGroupResource;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\SearchTermResource;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignApiController extends BaseApiController
{
    /**
     * Listar todas as campanhas.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Campaign::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->filled('channel_type')) {
            $query->where('advertising_channel_type', $request->input('channel_type'));
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $perPage = $request->input('per_page', 50);
        $perPage = min(max((int) $perPage, 1), 500);

        $campaigns = $query->withCount('adGroups')->paginate($perPage);

        return $this->paginatedResponse($campaigns, CampaignResource::class);
    }

    /**
     * Exibir uma campanha específica.
     */
    public function show(Campaign $campaign): JsonResponse
    {
        return $this->successResponse(new CampaignResource($campaign->load('adGroups')));
    }

    /**
     * Listar grupos de anúncios de uma campanha.
     */
    public function adGroups(Campaign $campaign, Request $request): JsonResponse
    {
        $query = $campaign->adGroups();

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $perPage = $request->input('per_page', 50);
        $perPage = min(max((int) $perPage, 1), 500);

        $adGroups = $query->paginate($perPage);

        return $this->paginatedResponse($adGroups, AdGroupResource::class);
    }

    /**
     * Listar termos de pesquisa de uma campanha.
     */
    public function searchTerms(Campaign $campaign, Request $request): JsonResponse
    {
        $query = $campaign->hasMany(\App\Models\SearchTerm::class, 'campaign_id', 'google_campaign_id');

        $perPage = $request->input('per_page', 50);
        $perPage = min(max((int) $perPage, 1), 1000);

        $searchTerms = $query->paginate($perPage);

        return $this->paginatedResponse($searchTerms, SearchTermResource::class);
    }

    /**
     * Obter estatísticas da campanha.
     */
    public function stats(Campaign $campaign): JsonResponse
    {
        $searchTermsQuery = \App\Models\SearchTerm::where('campaign_id', $campaign->google_campaign_id);

        $stats = [
            'campaign' => [
                'id' => $campaign->id,
                'google_campaign_id' => $campaign->google_campaign_id,
                'name' => $campaign->name,
            ],
            'ad_groups_count' => $campaign->adGroups()->count(),
            'active_ad_groups_count' => $campaign->adGroups()->active()->count(),
            'search_terms_count' => (clone $searchTermsQuery)->count(),
            'total_impressions' => (clone $searchTermsQuery)->sum('impressions'),
            'total_clicks' => (clone $searchTermsQuery)->sum('clicks'),
            'total_cost_micros' => (clone $searchTermsQuery)->sum('cost_micros'),
            'avg_ctr' => (clone $searchTermsQuery)->avg('ctr'),
        ];

        return $this->successResponse($stats);
    }
}
