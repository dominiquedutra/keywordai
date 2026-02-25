<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NegativeKeyword extends Model
{
    use HasFactory;

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'keyword',
        'match_type',
        'reason',
        'list_id',
        'resource_name',
        'created_by_id',
        'updated_by_id',
    ];

    /**
     * Obter o usuário que criou a palavra-chave negativa.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Obter o usuário que atualizou a palavra-chave negativa.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    /**
     * Escopo para filtrar palavras-chave negativas por tipo de correspondência.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $matchType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithMatchType($query, $matchType)
    {
        return $query->where('match_type', $matchType);
    }

    /**
     * Escopo para filtrar palavras-chave negativas por lista.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $listId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInList($query, $listId)
    {
        return $query->where('list_id', $listId);
    }

    /**
     * Escopo para filtrar palavras-chave negativas por criador.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by_id', $userId);
    }

    /**
     * Obter o tipo de correspondência formatado para exibição.
     *
     * @return string
     */
    public function getFormattedMatchTypeAttribute(): string
    {
        switch (strtolower($this->match_type)) {
            case 'broad':
                return 'Ampla (Broad)';
            case 'phrase':
                return 'Frase (Phrase)';
            case 'exact':
                return 'Exata (Exact)';
            default:
                return $this->match_type;
        }
    }
}
