<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchTerm extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'search_terms';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'search_term',
        'keyword_text',
        'match_type',
        'impressions',
        'clicks',
        'cost_micros',
        'ctr',
        'status',
        'campaign_id',
        'campaign_name',
        'ad_group_id',
        'ad_group_name',
        'first_seen_at',
        'statistics_synced_at',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'impressions' => 'integer',
        'clicks' => 'integer',
        'cost_micros' => 'integer',
        'ctr' => 'float',
        'first_seen_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'notified_at' => 'datetime', // Adicionado cast para notified_at
        'statistics_synced_at' => 'datetime', // Adicionado cast para statistics_synced_at
    ];

    /**
     * Escopo para filtrar termos de pesquisa por status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Escopo para filtrar termos de pesquisa por data de primeira visualização.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFirstSeenAt($query, $date)
    {
        return $query->whereDate('first_seen_at', $date);
    }

    /**
     * Escopo para filtrar termos de pesquisa por campanha.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $campaignId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCampaign($query, $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Escopo para filtrar termos de pesquisa por grupo de anúncios.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $adGroupId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAdGroup($query, $adGroupId)
    {
        return $query->where('ad_group_id', $adGroupId);
    }

    /**
     * Obter o custo formatado em reais.
     *
     * @return string
     */
    public function getFormattedCostAttribute()
    {
        return 'R$ ' . number_format($this->cost_micros / 1000000, 2, ',', '.');
    }

    /**
     * Obter a CTR formatada como porcentagem.
     *
     * @return string
     */
    public function getFormattedCtrAttribute()
    {
        return number_format($this->ctr, 2, ',', '.') . '%';
    }
}
