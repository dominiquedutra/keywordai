<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiAnalysisLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'source',
        'analysis_type',
        'date_filter',
        'filters',
        'settings_snapshot',
        'model',
        'model_name',
        'term_limit',
        'terms_found',
        'prompt',
        'prompt_size',
        'reply_code',
        'reply',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'duration',
        'success',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'settings_snapshot' => 'array',
        'date_filter' => 'date',
        'created_at' => 'datetime',
        'success' => 'boolean',
        'duration' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
