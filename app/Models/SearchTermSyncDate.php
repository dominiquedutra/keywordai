<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SearchTermSyncDate extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'search_term_sync_dates';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sync_date',
        'status',
        'job_id',
        'attempts',
        'last_error',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sync_date' => 'date',
        'attempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Escopo para filtrar por status.
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
     * Escopo para filtrar datas pendentes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Escopo para filtrar datas em processamento.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Escopo para filtrar datas concluídas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Escopo para filtrar datas com falha.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Escopo para filtrar datas que não estão concluídas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotCompleted($query)
    {
        return $query->whereNotIn('status', ['completed']);
    }

    /**
     * Escopo para filtrar datas que não estão em processamento ou concluídas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailableForProcessing($query)
    {
        return $query->whereNotIn('status', ['processing', 'completed']);
    }

    /**
     * Marca a data como em processamento e incrementa o contador de tentativas.
     *
     * @param string|null $jobId
     * @return bool
     */
    public function markAsProcessing($jobId = null)
    {
        $this->status = 'processing';
        $this->attempts = $this->attempts + 1;
        
        if ($jobId) {
            $this->job_id = $jobId;
        }
        
        return $this->save();
    }

    /**
     * Marca a data como concluída.
     *
     * @return bool
     */
    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->last_error = null;
        return $this->save();
    }

    /**
     * Marca a data como falha.
     *
     * @param string $errorMessage
     * @return bool
     */
    public function markAsFailed($errorMessage)
    {
        $this->status = 'failed';
        $this->last_error = $errorMessage;
        return $this->save();
    }

    /**
     * Reseta o status para pendente.
     *
     * @param bool $resetAttempts Se deve zerar o contador de tentativas
     * @return bool
     */
    public function resetToPending($resetAttempts = false)
    {
        $this->status = 'pending';
        
        if ($resetAttempts) {
            $this->attempts = 0;
        }
        
        return $this->save();
    }
}
