<?php

namespace App\Console\Commands;

use App\Jobs\SyncSearchTermStatsJob;
use App\Models\SearchTerm;
use Illuminate\Console\Command;

class SyncSearchTermStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'googleads:sync-term-stats {term : O termo de pesquisa a ser sincronizado}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza as estatísticas de um termo de pesquisa específico com o Google Ads';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Obter o termo de pesquisa do argumento
        $term = $this->argument('term');
        $this->info("Buscando o termo de pesquisa: '{$term}'");

        // Buscar TODAS as instâncias do SearchTerm no banco de dados
        $searchTerms = SearchTerm::where('search_term', $term)->get();

        // Verificar se o termo foi encontrado
        if ($searchTerms->isEmpty()) {
            $this->error("Termo de pesquisa '{$term}' não encontrado no banco de dados local.");
            $this->line("Certifique-se de que o termo existe na tabela search_terms.");
            return Command::FAILURE;
        }

        $count = $searchTerms->count();
        $this->info("Encontradas {$count} ocorrências do termo '{$term}' no banco de dados.");
        
        // Despachar um job para cada instância do termo
        $this->line("Despachando jobs para sincronizar estatísticas de cada ocorrência...");
        
        $jobsDispatched = 0;
        foreach ($searchTerms as $searchTerm) {
            $this->line("- Despachando job para o termo '{$term}' na campanha {$searchTerm->campaign_id}, grupo de anúncios {$searchTerm->ad_group_id} (ID: {$searchTerm->id})");
            SyncSearchTermStatsJob::dispatch($searchTerm)->onQueue('default');
            $jobsDispatched++;
        }
        
        $this->info("{$jobsDispatched} jobs despachados com sucesso!");
        $this->line("As estatísticas para todas as ocorrências de '{$term}' serão atualizadas em breve.");
        $this->line("Certifique-se de que o worker da fila está rodando: php artisan queue:work");
        
        return Command::SUCCESS;
    }
}
