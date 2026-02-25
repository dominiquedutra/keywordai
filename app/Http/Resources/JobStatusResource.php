<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobStatusResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'job_id' => $this['job_id'] ?? null,
            'status' => $this['status'] ?? 'unknown',
            'queue' => $this['queue'] ?? 'default',
            'attempts' => $this['attempts'] ?? 0,
            'created_at' => $this['created_at'] ?? null,
            'available_at' => $this['available_at'] ?? null,
            'message' => $this['message'] ?? null,
        ];
    }
}
