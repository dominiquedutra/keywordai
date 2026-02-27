<?php

namespace App\Console\Commands;

use App\Models\AiAnalysisLog;
use Illuminate\Console\Command;

class PurgeAiAnalysisLogsCommand extends Command
{
    protected $signature = 'keywordai:purge-ai-logs
                            {--days=7 : Purgar logs com mais de N dias}';

    protected $description = 'Remove logs de análise de IA com mais de N dias (padrão: 7)';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $count = AiAnalysisLog::where('created_at', '<', $cutoff)->count();

        if ($count === 0) {
            $this->info("Nenhum log com mais de {$days} dias para remover.");
            return Command::SUCCESS;
        }

        AiAnalysisLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Removidos {$count} log(s) de análise de IA com mais de {$days} dias.");
        return Command::SUCCESS;
    }
}
