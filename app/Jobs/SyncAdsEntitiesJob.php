<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SyncAdsEntitiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Criar uma nova instância do job.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Executar o job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('Iniciando SyncAdsEntitiesJob');
            
            // Executar o comando de sincronização
            $exitCode = Artisan::call('googleads:sync-entities');
            
            if ($exitCode === 0) {
                Log::info('SyncAdsEntitiesJob concluído com sucesso');
            } else {
                Log::error("SyncAdsEntitiesJob falhou com código de saída {$exitCode}");
                $this->fail(new \Exception("O comando googleads:sync-entities falhou com código de saída {$exitCode}"));
            }
        } catch (\Exception $e) {
            Log::error("Erro em SyncAdsEntitiesJob: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            $this->fail($e);
        }
    }
}
