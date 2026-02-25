<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'google_campaign_id',
        'resource_name',
        'name',
        'status',
        'start_date',
        'end_date',
        'advertising_channel_type',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Obter os grupos de anúncios associados a esta campanha.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function adGroups(): HasMany
    {
        return $this->hasMany(AdGroup::class);
    }

    /**
     * Escopo para filtrar campanhas ativas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'ENABLED');
    }
}
