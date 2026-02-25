<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AdGroupResource;
use App\Http\Resources\SearchTermResource;
use App\Models\AdGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdGroupApiController extends BaseApiController
{
    /**
     * Listar todos os grupos de anúncios.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AdGroup::with('campaign');

        if ($request->filled('campaign_id')) {
            $query->whereHas('campaign', function ($q) use ($request) {
                $q->where('google_campaign_id', $request->input('campaign_id'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortBy, $sortDirection);

        $perPage = $request->input('per_page', 50);
        $perPage = min(max((int) $perPage, 1), 500);

        $adGroups = $query->paginate($perPage);

        return $this->paginatedResponse($adGroups, AdGroupResource::class);
    }

    /**
     * Exibir um grupo de anúncios específico.
     */
    public function show(AdGroup $adGroup): JsonResponse
    {
        return $this->successResponse(new AdGroupResource($adGroup->load('campaign')));
    }

    /**
     * Listar termos de pesquisa de um grupo de anúncios.
     */
    public function searchTerms(AdGroup $adGroup, Request $request): JsonResponse
    {
        $query = \App\Models\SearchTerm::where('ad_group_id', $adGroup->google_ad_group_id);

        $perPage = $request->input('per_page', 50);
        $perPage = min(max((int) $perPage, 1), 1000);

        $searchTerms = $query->paginate($perPage);

        return $this->paginatedResponse($searchTerms, SearchTermResource::class);
    }
}
