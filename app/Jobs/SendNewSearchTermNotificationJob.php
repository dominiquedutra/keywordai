<?php

namespace App\Jobs;

use App\Services\GoogleChatNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendNewSearchTermNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * O número de vezes que o job pode ser tentado.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * O número de segundos que o job pode rodar antes de timeout.
     *
     * @var int
     */
    public int $timeout = 120;

    /**
     * Indica se o job deve ser deletado se ocorrer uma exceção não capturada.
     *
     * @var bool
     */
    public bool $failOnTimeout = true;

    protected string $searchTermText;
    protected string $campaignName;
    protected string $adGroupName;
    protected string $keywordText;
    protected mixed $searchTermId;
    protected mixed $campaignId;
    protected mixed $adGroupId;
    protected string $negativeListId;

    /**
     * Cria uma nova instância do job.
     *
     * @param string $searchTermText
     * @param string $campaignName
     * @param string $adGroupName
     * @param string $keywordText
     * @param mixed $searchTermId
     * @param mixed $campaignId
     * @param mixed|null $adGroupId
     * @param string $negativeListId
     */
    public function __construct(
        string $searchTermText,
        string $campaignName,
        string $adGroupName = 'Grupo não especificado',
        string $keywordText = 'Palavra-chave não especificada',
        mixed $searchTermId = 0,
        mixed $campaignId = 0,
        mixed $adGroupId = null,
        string $negativeListId = 'XYZ'
    ) {
        $this->searchTermText = $searchTermText;
        $this->campaignName = $campaignName;
        $this->adGroupName = $adGroupName;
        $this->keywordText = $keywordText;
        $this->searchTermId = $searchTermId;
        $this->campaignId = $campaignId;
        $this->adGroupId = $adGroupId;
        $this->negativeListId = $negativeListId;
    }

    /**
     * Executa o job.
     *
     * @param GoogleChatNotificationService $notificationService
     * @return void
     */
    public function handle(GoogleChatNotificationService $notificationService): void
    {
        Log::info('Executando SendNewSearchTermNotificationJob', ['term' => $this->searchTermText]);

        // Verifica se as notificações do Google Chat estão habilitadas
        if (!config('app.send_google_chat_notifications')) {
            Log::info('Notificação para o termo de pesquisa suprimida devido à configuração', [
                'term' => $this->searchTermText,
                'config' => 'app.send_google_chat_notifications=false'
            ]);
            return; // Sai do job sem enviar a notificação
        }

        try {
            $notificationService->sendNewSearchTermNotification(
                $this->searchTermText,
                $this->campaignName,
                $this->adGroupName,
                $this->keywordText,
                $this->searchTermId,
                $this->campaignId,
                $this->adGroupId,
                $this->negativeListId
            );
        } catch (Throwable $e) {
            Log::error('Erro dentro do SendNewSearchTermNotificationJob->handle()', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'term' => $this->searchTermText,
            ]);
            // Opcional: Falhar o job explicitamente para que ele não seja tentado novamente se o erro for irrecuperável
            // $this->fail($e);
            // Ou apenas relançar a exceção para usar a lógica de retentativa padrão do Laravel
            throw $e;
        }
    }

    /**
     * Manipula uma falha no job.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::critical('SendNewSearchTermNotificationJob falhou permanentemente após tentativas.', [
            'exception_message' => $exception->getMessage(),
            'term' => $this->searchTermText,
            'campaign' => $this->campaignName,
        ]);
        // Aqui você pode adicionar lógica adicional, como notificar um administrador sobre a falha permanente.
    }
}
