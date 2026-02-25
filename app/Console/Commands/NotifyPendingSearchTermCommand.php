<?php

namespace App\Console\Commands;

use App\Jobs\SendNewSearchTermNotificationJob;
use App\Models\SearchTerm;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class NotifyPendingSearchTermCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keywordai:notify-pending-search-term';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca o próximo termo de pesquisa não notificado e envia a notificação via Google Chat.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Procurando por termo de pesquisa pendente de notificação...');

        // Busca o primeiro termo não notificado, ordenado pelo ID (ou created_at)
        $searchTerm = SearchTerm::whereNull('notified_at')
                                ->orderBy('id', 'asc') // Garante ordem FIFO
                                ->first();

        if (!$searchTerm) {
            $this->info('Nenhum termo de pesquisa pendente encontrado.');
            return Command::SUCCESS;
        }

        $this->info("Termo encontrado: '{$searchTerm->search_term}' (ID: {$searchTerm->id}). Disparando notificação...");

        try {
            // Dispara o Job para enviar a notificação
            // Precisamos dos dados corretos aqui. Assumindo que o modelo SearchTerm
            // tem acesso direto ou via relações aos nomes/IDs necessários.
            // Se não tiver, precisaria carregar relações (ex: $searchTerm->load('campaign', 'adGroup'))
            // ou ajustar a query inicial para buscar os dados necessários.
            // Por enquanto, usaremos os campos disponíveis no modelo SearchTerm.
            // ATENÇÃO: O modelo SearchTerm atual não tem 'keyword_text'. Isso precisará ser ajustado
            // quando a lógica de armazenamento for implementada. Usaremos um placeholder por agora.

            $keywordTextPlaceholder = $searchTerm->keyword_text ?? '[Palavra-Chave Não Disponível]'; // Placeholder

            SendNewSearchTermNotificationJob::dispatch(
                $searchTerm->search_term,
                $searchTerm->campaign_name, // Assumindo que campaign_name está no modelo
                $searchTerm->ad_group_name, // Assumindo que ad_group_name está no modelo
                $keywordTextPlaceholder,    // Usando placeholder
                $searchTerm->id,
                $searchTerm->campaign_id, // Assumindo que campaign_id está no modelo
                $searchTerm->ad_group_id  // Assumindo que ad_group_id está no modelo
            )->onQueue('notifications');

            // Marca o termo como notificado
            $searchTerm->notified_at = Carbon::now();
            $searchTerm->save();

            $this->info("Notificação para '{$searchTerm->search_term}' (ID: {$searchTerm->id}) disparada e termo marcado como notificado.");

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            Log::error("Erro ao processar notificação para termo ID {$searchTerm->id}", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error("Erro ao processar notificação para termo ID {$searchTerm->id}: " . $e->getMessage());
            // Não marca como notificado em caso de erro no dispatch/save
            return Command::FAILURE;
        }
    }
}
