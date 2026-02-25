<?php

namespace App\Console;

use App\Jobs\SyncAdsEntitiesJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Comando de notificação removido - as notificações agora são enviadas diretamente pelo SyncSearchTermsForDateJob
        // quando novos termos são encontrados, baseado na configuração SEND_GOOGLE_CHAT_NOTIFICATIONS
        
        // Adiciona o job para sincronizar campanhas e grupos de anúncios
        $schedule->job((new SyncAdsEntitiesJob())->onQueue('default'))
                 ->hourly() // Executa a cada hora
                 ->withoutOverlapping() // Evita sobreposição se a execução anterior demorar
                 ->onOneServer(); // Garante que o job seja executado apenas em um servidor
        
        // Adiciona o job para sincronizar termos de pesquisa para o dia atual
        $schedule->job((new \App\Jobs\SyncSearchTermsForDateJob(\Carbon\Carbon::today()))->onQueue('default'))
                 ->everyTenMinutes() // Executa a cada 10 minutos
                 ->withoutOverlapping() // Evita sobreposição se a execução anterior demorar
                 ->onOneServer(); // Garante que o job seja executado apenas em um servidor
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
