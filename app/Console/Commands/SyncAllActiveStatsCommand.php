<?php

namespace App\Console\Commands;

use App\Jobs\BatchSyncSearchTermStatsJob;
use App\Models\SearchTerm;
use Illuminate\Console\Command;

class SyncAllActiveStatsCommand extends Command
{
    protected $signature = 'keywordai:sync-all-active-stats
                            {--queue=default : Nome da fila para o job}
                            {--dry-run : Apenas mostrar o que seria feito}';

    protected $description = 'Sincroniza estatísticas de todos os termos de pesquisa ativos via batch (1 chamada à API)';

    public function handle(): int
    {
        $queue = $this->option('queue');
        $dryRun = $this->option('dry-run');

        $activeCount = SearchTerm::where('status', '!=', 'EXCLUDED')->count();

        if ($activeCount === 0) {
            $this->warn('Nenhum termo de pesquisa ativo encontrado.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info("Modo dry-run: {$activeCount} termos ativos seriam sincronizados em 1 job batch (1 chamada à API).");
            $this->info("Fila: {$queue}");
            return self::SUCCESS;
        }

        BatchSyncSearchTermStatsJob::dispatch()->onQueue($queue);

        $this->info("1 BatchSyncSearchTermStatsJob enfileirado na fila '{$queue}' para sincronizar {$activeCount} termos ativos.");

        return self::SUCCESS;
    }
}
