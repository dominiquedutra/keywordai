<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use DateTimeZone;

class GoogleAdsQuotaService
{
    /**
     * Limite diário de requisições (15.000 no tier standard)
     */
    const DAILY_LIMIT = 14000;

    /**
     * Limite de requisições por minuto
     */
    const PER_MINUTE_LIMIT = 60;

    /**
     * Timezone do Pacific Time (onde o limite diário reseta à meia-noite)
     */
    const PACIFIC_TIMEZONE = 'America/Los_Angeles';

    /**
     * Verifica se é possível fazer mais requisições à API
     * 
     * @param int $requests Número de requisições a serem feitas
     * @return bool True se há cota disponível, False caso contrário
     */
    public function canMakeRequest(int $requests = 1): bool
    {
        // Verifica o limite diário
        if ($this->getDailyUsage() + $requests > self::DAILY_LIMIT) {
            return false;
        }

        // Verifica o limite por minuto
        if ($this->getPerMinuteUsage() + $requests > self::PER_MINUTE_LIMIT) {
            return false;
        }

        return true;
    }

    /**
     * Registra uma requisição à API
     * 
     * @param int $requests Número de requisições feitas
     * @return void
     */
    public function recordRequest(int $requests = 1): void
    {
        DB::table('api_request_logs')->insert([
            'created_at' => Carbon::now(),
            'requests_made' => $requests
        ]);
    }

    /**
     * Calcula o total de requisições feitas desde a meia-noite no Horário do Pacífico
     * 
     * @return int Total de requisições feitas hoje
     */
    public function getDailyUsage(): int
    {
        $startOfDayPT = $this->getStartOfDayPacificTime();
        
        return DB::table('api_request_logs')
            ->where('created_at', '>=', $startOfDayPT)
            ->sum('requests_made');
    }

    /**
     * Calcula o total de requisições feitas no último minuto
     * 
     * @return int Total de requisições feitas no último minuto
     */
    public function getPerMinuteUsage(): int
    {
        $oneMinuteAgo = Carbon::now()->subMinute();
        
        return DB::table('api_request_logs')
            ->where('created_at', '>=', $oneMinuteAgo)
            ->sum('requests_made');
    }

    /**
     * Obtém o timestamp do início do dia atual no Horário do Pacífico
     * 
     * @return Carbon
     */
    private function getStartOfDayPacificTime(): Carbon
    {
        return Carbon::now()
            ->setTimezone(self::PACIFIC_TIMEZONE)
            ->startOfDay()
            ->setTimezone(config('app.timezone'));
    }
}
