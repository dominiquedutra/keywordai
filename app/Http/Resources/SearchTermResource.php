<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchTermResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'search_term' => $this->search_term,
            'keyword_text' => $this->keyword_text,
            'match_type' => $this->match_type,
            'status' => $this->status,
            'impressions' => $this->impressions,
            'clicks' => $this->clicks,
            'cost_micros' => $this->cost_micros,
            'cost_formatted' => $this->formatted_cost,
            'ctr' => $this->ctr,
            'ctr_formatted' => $this->formatted_ctr,
            'campaign' => [
                'id' => $this->campaign_id,
                'name' => $this->campaign_name,
            ],
            'ad_group' => [
                'id' => $this->ad_group_id,
                'name' => $this->ad_group_name,
            ],
            'first_seen_at' => $this->first_seen_at?->format('Y-m-d'),
            'statistics_synced_at' => $this->statistics_synced_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
