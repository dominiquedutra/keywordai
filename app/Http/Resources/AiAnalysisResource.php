<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiAnalysisResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'model' => $this['model'] ?? null,
            'analysis_date' => $this['analysis_date'] ?? now()->format('Y-m-d H:i:s'),
            'total_terms_analyzed' => $this['total_terms_analyzed'] ?? 0,
            'terms' => $this['terms'] ?? [],
            'summary' => $this['summary'] ?? null,
            'raw_response' => $this->when($request->boolean('include_raw'), $this['raw_response'] ?? null),
        ];
    }
}
