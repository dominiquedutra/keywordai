<?php

namespace App\Console\Commands;

use App\Jobs\SyncSearchTermStatsJob;
use App\Models\SearchTerm;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class QueueSyncStatsForDateRangeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keywordai:queue-sync-stats
                            {--start-date= : Data inicial (YYYY-MM-DD), padrão: 7 dias atrás}
                            {--end-date= : Data final (YYYY-MM-DD), padrão: hoje}
                            {--chunk-size=100 : Quantidade de termos a processar por vez}
                            {--queue=default : Nome da fila para enfileirar os jobs}
                            {--dry-run : Apenas simula a execução sem enfileirar jobs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enfileira jobs SyncSearchTermStatsJob para todos os termos de pesquisa em um intervalo de datas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Obter as datas das opções
        $startDateOption = $this->option('start-date');
        $endDateOption = $this->option('end-date');
        $chunkSize = (int) $this->option('chunk-size');
        $queueName = $this->option('queue');
        $dryRun = $this->option('dry-run');

        // Definir datas padrão se não fornecidas
        if (empty($startDateOption)) {
            $startDate = Carbon::now()->subDays(7)->startOfDay();
            $this->info("Usando data padrão para início: {$startDate->format('Y-m-d')} (7 dias atrás)");
        } else {
            try {
                $startDate = Carbon::parse($startDateOption)->startOfDay();
            } catch (\Exception $e) {
                $this->error('Formato de data inicial inválido. Use YYYY-MM-DD.');
                return Command::FAILURE;
            }
        }

        if (empty($endDateOption)) {
            $endDate = Carbon::now()->endOfDay();
            $this->info("Usando data padrão para fim: {$endDate->format('Y-m-d')} (hoje)");
        } else {
            try {
                $endDate = Carbon::parse($endDateOption)->endOfDay();
            } catch (\Exception $e) {
                $this->error('Formato de data final inválido. Use YYYY-MM-DD.');
                return Command::FAILURE;
            }
        }

        // Validar intervalo de datas
        if ($endDate->isBefore($startDate)) {
            $this->error('A data final não pode ser anterior à data inicial.');
            return Command::FAILURE;
        }

        $this->info("Buscando termos de pesquisa com first_seen_at entre {$startDate->format('Y-m-d')} e {$endDate->format('Y-m-d')}...");

        // Contar total de termos no intervalo
        $totalTerms = SearchTerm::whereBetween('first_seen_at', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])->count();
        
        if ($totalTerms === 0) {
            $this->warn("Nenhum termo de pesquisa encontrado no intervalo de datas especificado.");
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$totalTerms} termos de pesquisa no intervalo.");

        if ($dryRun) {
            $this->info("Modo simulação (dry-run) ativado. Nenhum job será enfileirado.");
            return Command::SUCCESS;
        }

        // Inicializar contador
        $jobsQueued = 0;

        // Processar em chunks para evitar problemas de memória
        SearchTerm::whereBetween('first_seen_at', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->chunkById($chunkSize, function ($searchTerms) use (&$jobsQueued, $queueName) {
                foreach ($searchTerms as $searchTerm) {
                    // Enfileirar o job
                    SyncSearchTermStatsJob::dispatch($searchTerm)
                        ->onQueue($queueName);
                    
                    $jobsQueued++;
                    
                    // Feedback visual a cada 100 jobs
                    if ($jobsQueued % 100 === 0) {
                        $this->info("Enfileirados {$jobsQueued} jobs até o momento...");
                    }
                }
            });

        $this->info("Total de {$jobsQueued} jobs SyncSearchTermStatsJob enfileirados com sucesso na fila '{$queueName}'.");
        
        // Registrar no log
        Log::info("QueueSyncStatsForDateRangeCommand: {$jobsQueued} jobs enfileirados para o intervalo {$startDate->format('Y-m-d')} a {$endDate->format('Y-m-d')}");

        return Command::SUCCESS;
    }
}
