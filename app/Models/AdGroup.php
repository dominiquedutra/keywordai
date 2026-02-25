<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdGroup extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'google_ad_group_id',
        'resource_name',
        'name',
        'status',
        'campaign_id',
    ];

    /**
     * Obter a campanha associada a este grupo de anúncios.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Escopo para filtrar grupos de anúncios ativos.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ENABLED');
    }

    /**
     * Obter o nome formatado com o nome da campanha entre colchetes.
     *
     * @return string
     */
    public function getFormattedNameAttribute(): string
    {
        $campaignName = $this->campaign ? $this->campaign->name : 'Campanha Desconhecida';
        return "{$this->name} [{$campaignName}]";
    }
}
