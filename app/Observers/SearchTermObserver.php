<?php

namespace App\Observers;

use App\Jobs\SyncSearchTermStatsJob;
use App\Models\SearchTerm;
use Illuminate\Support\Facades\Log;

class SearchTermObserver
{
    /**
     * Handle the SearchTerm "created" event.
     * Dispara o job para sincronizar as estatísticas do termo de pesquisa.
     */
    public function created(SearchTerm $searchTerm): void
    {
        Log::info("SearchTermObserver: Novo SearchTerm criado, ID: {$searchTerm->id}, Termo: '{$searchTerm->search_term}'");
        
        // Despachar o job para sincronizar as estatísticas na fila 'default'
        SyncSearchTermStatsJob::dispatch($searchTerm)->onQueue('default');
        
        Log::info("SearchTermObserver: Job SyncSearchTermStatsJob despachado para o termo: '{$searchTerm->search_term}'");
    }

    /**
     * Handle the SearchTerm "updated" event.
     */
    public function updated(SearchTerm $searchTerm): void
    {
        // Não precisamos fazer nada aqui por enquanto
    }

    /**
     * Handle the SearchTerm "deleted" event.
     */
    public function deleted(SearchTerm $searchTerm): void
    {
        // Não precisamos fazer nada aqui por enquanto
    }

    /**
     * Handle the SearchTerm "restored" event.
     */
    public function restored(SearchTerm $searchTerm): void
    {
        // Não precisamos fazer nada aqui por enquanto
    }

    /**
     * Handle the SearchTerm "force deleted" event.
     */
    public function forceDeleted(SearchTerm $searchTerm): void
    {
        // Não precisamos fazer nada aqui por enquanto
    }
}
