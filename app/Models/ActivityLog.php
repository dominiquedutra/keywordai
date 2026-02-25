<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * Indica se o modelo deve ser timestampable.
     * Como estamos usando apenas created_at, desativamos o updated_at.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'action_type',
        'entity_type',
        'entity_id',
        'ad_group_id',
        'ad_group_name',
        'campaign_id',
        'campaign_name',
        'details',
        'created_at',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Obter o usuário que realizou a ação.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Escopo para filtrar logs por tipo de ação.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $actionType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Escopo para filtrar logs por tipo de entidade.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $entityType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithEntityType($query, $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Escopo para filtrar logs por usuário.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Escopo para filtrar logs por data.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('created_at', $date);
    }

    /**
     * Escopo para filtrar logs por período.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereDate('created_at', '>=', $startDate)
                     ->whereDate('created_at', '<=', $endDate);
    }

    /**
     * Obter o tipo de ação formatado para exibição.
     *
     * @return string
     */
    public function getFormattedActionTypeAttribute(): string
    {
        switch ($this->action_type) {
            case 'add_keyword':
                return 'Adição de Palavra-chave';
            case 'add_negative_keyword':
                return 'Adição de Palavra-chave Negativa';
            default:
                return $this->action_type;
        }
    }
}
