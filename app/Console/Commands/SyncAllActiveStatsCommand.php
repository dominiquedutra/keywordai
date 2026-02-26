<?php

namespace App\Console\Commands;

use App\Jobs\SyncSearchTermStatsJob;
use App\Models\SearchTerm;
use Illuminate\Console\Command;

class SyncAllActiveStatsCommand extends Command
{
    protected $signature = 'keywordai:sync-all-active-stats
                            {--queue=default : Nome da fila para os jobs}
                            {--chunk-size=100 : Quantidade de termos por lote}
                            {--dry-run : Apenas mostrar quantos termos seriam sincronizados}';

    protected $description = 'Sincroniza estatísticas de todos os termos de pesquisa ativos (não excluídos)';

    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk-size');
        $queue = $this->option('queue');
        $dryRun = $this->option('dry-run');

        $query = SearchTerm::where('status', '!=', 'EXCLUDED');
        $total = $query->count();

        if ($total === 0) {
            $this->warn('Nenhum termo de pesquisa ativo encontrado.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info("Modo dry-run: {$total} termos seriam sincronizados em lotes de {$chunkSize}.");
            $this->info("Fila: {$queue}");
            return self::SUCCESS;
        }

        $this->info("Enfileirando sincronização de estatísticas para {$total} termos...");

        $dispatched = 0;

        $query->chunkById($chunkSize, function ($terms) use ($queue, &$dispatched) {
            foreach ($terms as $term) {
                SyncSearchTermStatsJob::dispatch($term)->onQueue($queue);
                $dispatched++;
            }
        });

        $this->info("Concluído: {$dispatched} jobs enfileirados na fila '{$queue}'.");

        return self::SUCCESS;
    }
}
