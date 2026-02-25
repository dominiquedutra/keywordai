<?php

namespace App\Console\Commands;

use App\Jobs\SyncSearchTermsForDateJob;
use App\Models\SearchTermSyncDate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FullSyncSearchTermsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'googleads:full-sync-search-terms
                            {--force-retry : Força a reexecução de datas com status "failed"}
                            {--dry-run : Simula a execução sem despachar os jobs}
                            {--limit= : Limita o número de jobs despachados de uma vez}
                            {--sleep= : Tempo de espera em milissegundos entre o despacho de cada job (padrão: 100ms)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza termos de pesquisa dia a dia desde GOOGLE_ADS_ABSOLUTE_START_DATE até ontem';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando processo de sincronização completa de termos de pesquisa...');
        
        // Obter a data inicial absoluta da configuração
        $startDate = Carbon::parse(config('googleads.absolute_start_date', '2000-01-01'));
        $this->info("Data inicial absoluta: {$startDate->format('Y-m-d')}");
        
        // Obter a data final (ontem)
        $endDate = Carbon::yesterday();
        $this->info("Data final: {$endDate->format('Y-m-d')}");
        
        // Verificar se a data inicial é posterior à data final
        if ($startDate->isAfter($endDate)) {
            $this->error('A data inicial é posterior à data final.');
            return Command::FAILURE;
        }
        
        // Gerar o intervalo de datas
        $dateRange = CarbonPeriod::create($startDate, $endDate);
        $totalDates = iterator_count($dateRange);
        $this->info("Total de datas no intervalo: {$totalDates}");
        
        // Buscar as datas já sincronizadas no banco de dados
        $completedDates = SearchTermSyncDate::completed()
            ->pluck('sync_date')
            ->map(function ($date) {
                return $date->format('Y-m-d');
            })
            ->toArray();
        
        $processingDates = SearchTermSyncDate::processing()
            ->pluck('sync_date')
            ->map(function ($date) {
                return $date->format('Y-m-d');
            })
            ->toArray();
        
        $failedDates = SearchTermSyncDate::failed()
            ->pluck('sync_date')
            ->map(function ($date) {
                return $date->format('Y-m-d');
            })
            ->toArray();
        
        $pendingDates = SearchTermSyncDate::pending()
            ->pluck('sync_date')
            ->map(function ($date) {
                return $date->format('Y-m-d');
            })
            ->toArray();
        
        $this->info("Datas já concluídas: " . count($completedDates));
        $this->info("Datas em processamento: " . count($processingDates));
        $this->info("Datas com falha: " . count($failedDates));
        $this->info("Datas pendentes: " . count($pendingDates));
        
        // Identificar as datas pendentes
        $datesToProcess = [];
        $forceRetry = $this->option('force-retry');
        
        foreach ($dateRange as $date) {
            $dateFormatted = $date->format('Y-m-d');
            
            // Pular datas já concluídas
            if (in_array($dateFormatted, $completedDates)) {
                continue;
            }
            
            // Pular datas em processamento
            if (in_array($dateFormatted, $processingDates)) {
                continue;
            }
            
            // Pular datas com falha, a menos que force-retry esteja ativado
            if (in_array($dateFormatted, $failedDates) && !$forceRetry) {
                continue;
            }
            
            $datesToProcess[] = $dateFormatted;
        }
        
        $this->info("Datas a serem processadas: " . count($datesToProcess));
        
        // Aplicar limite se especificado
        $limit = $this->option('limit');
        if ($limit && is_numeric($limit) && $limit > 0) {
            $datesToProcess = array_slice($datesToProcess, 0, (int)$limit);
            $this->info("Limitando a {$limit} datas devido à opção --limit.");
        }
        
        // Verificar se é uma execução simulada
        $dryRun = $this->option('dry-run');
        if ($dryRun) {
            $this->info("Modo de simulação (--dry-run). Nenhum job será despachado.");
            $this->table(
                ['Data', 'Status'],
                array_map(function ($date) {
                    return [$date, 'Seria processada'];
                }, $datesToProcess)
            );
            return Command::SUCCESS;
        }
        
        // Obter o tempo de espera entre jobs
        $sleep = $this->option('sleep') ?? 100; // Padrão: 100ms
        
        // Criar registros na tabela search_term_sync_dates e despachar jobs
        $jobsDispatched = 0;
        $bar = $this->output->createProgressBar(count($datesToProcess));
        $bar->start();
        
        foreach ($datesToProcess as $dateFormatted) {
            // Verificar se o registro já existe
            $syncDate = SearchTermSyncDate::where('sync_date', $dateFormatted)->first();
            
            // Se o registro existir e não estiver com status 'pending' ou 'failed' (com force-retry), pular
            if ($syncDate) {
                if ($syncDate->status === 'failed' && $forceRetry) {
                    // Se a data estava com falha e force-retry está ativado, resetar para pendente
                    $syncDate->resetToPending($forceRetry);
                    $this->info("Resetando data {$dateFormatted} com status 'failed' para 'pending'.");
                } elseif ($syncDate->status !== 'pending') {
                    // Se o status não for 'pending', pular para a próxima data
                    $this->info("Pulando data {$dateFormatted} com status '{$syncDate->status}'.");
                    continue;
                }
            }
            
            // Não criamos o registro aqui - o job será responsável por isso
            
            // Despachar o job na fila 'bulk'
            $job = new SyncSearchTermsForDateJob(Carbon::parse($dateFormatted));
            dispatch($job)->onQueue('bulk');
            
            $jobsDispatched++;
            $bar->advance();
            
            // Pequena pausa para não sobrecarregar a fila
            if ($sleep > 0) {
                usleep($sleep * 1000); // Converter milissegundos para microssegundos
            }
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("Processo concluído. {$jobsDispatched} jobs despachados para a fila.");
        
        // Registrar no log
        Log::info("Full Sync iniciado via comando", [
            'total_dates' => $totalDates,
            'completed_dates' => count($completedDates),
            'processing_dates' => count($processingDates),
            'failed_dates' => count($failedDates),
            'pending_dates' => count($pendingDates),
            'dates_to_process' => count($datesToProcess),
            'jobs_dispatched' => $jobsDispatched,
            'force_retry' => $forceRetry,
            'limit' => $limit,
        ]);
        
        return Command::SUCCESS;
    }
}
