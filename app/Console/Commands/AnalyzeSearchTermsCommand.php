<?php

namespace App\Console\Commands;

use App\Services\AiAnalysisService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AnalyzeSearchTermsCommand extends Command
{
    /**
     * O nome e a assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'keywordai:analyze-search-terms
                            {--date= : A data específica para análise (formato YYYY-MM-DD) (Obrigatório)}
                            {--min-impressions=0 : Filtrar termos com impressões >= a este valor (Opcional)}
                            {--min-clicks=0 : Filtrar termos com cliques >= a este valor (Opcional)}
                            {--min-cost=0 : Filtrar termos com custo >= a este valor (em R$) (Opcional)}
                            {--model=gemini : O modelo de IA a ser usado (gemini, openai, perplexity) (Opcional)}
                            {--limit=50 : Limitar o número de termos enviados para análise (Opcional)}
                            {--show-prompt : Exibir o prompt gerado antes de enviar à IA (Opcional)}';

    /**
     * A descrição do comando.
     *
     * @var string
     */
    protected $description = 'Analisa termos de pesquisa com status NONE usando IA para identificar candidatos à negativação';

    /**
     * O serviço de análise de IA.
     *
     * @var \App\Services\AiAnalysisService
     */
    protected $aiAnalysisService;

    /**
     * Cria uma nova instância do comando.
     *
     * @param \App\Services\AiAnalysisService $aiAnalysisService
     * @return void
     */
    public function __construct(AiAnalysisService $aiAnalysisService)
    {
        parent::__construct();
        $this->aiAnalysisService = $aiAnalysisService;
    }

    /**
     * Executa o comando.
     */
    public function handle()
    {
        // Validar a data
        $date = $this->option('date');
        if (empty($date)) {
            $this->error('A opção --date é obrigatória (formato YYYY-MM-DD).');
            return Command::FAILURE;
        }

        try {
            $parsedDate = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Exception $e) {
            $this->error('Formato de data inválido. Use YYYY-MM-DD.');
            return Command::FAILURE;
        }

        // Obter os filtros e opções
        $filters = [
            'min_impressions' => (int) $this->option('min-impressions'),
            'min_clicks' => (int) $this->option('min-clicks'),
            'min_cost' => (float) $this->option('min-cost')
        ];
        
        $limit = (int) $this->option('limit');
        $model = strtolower($this->option('model'));
        $showPrompt = $this->option('show-prompt');

        // Informar ao usuário o que estamos fazendo
        $this->info("Coletando termos de pesquisa para a data {$date}...");

        try {
            // Obter o prompt para exibição, se solicitado
            if ($showPrompt) {
                // Obter o prompt diretamente do serviço
                $prompt = $this->aiAnalysisService->buildPrompt(
                    $this->aiAnalysisService->collectSearchTerms($parsedDate, $limit, $filters),
                    $this->aiAnalysisService->collectNegativeKeywords(),
                    $this->aiAnalysisService->collectPositiveKeywords(),
                    config('ai.instructions.global', ''),
                    config("ai.instructions.{$model}", ''),
                    $parsedDate
                );
                
                $this->info("Prompt gerado:");
                $this->line($prompt);
                
                if (!$this->confirm('Deseja continuar e enviar este prompt para a IA?')) {
                    $this->info('Operação cancelada pelo usuário.');
                    return Command::SUCCESS;
                }
            }

            // Chamar o serviço para analisar os termos
            $result = $this->aiAnalysisService->analyze($model, $limit, $filters, $parsedDate);
            
            if (!$result['success']) {
                $this->info($result['message']);
                return Command::SUCCESS;
            }
            
            // Exibir os resultados
            $this->displayResults($result['data']);
            
            // Exibir métricas da API
            $metrics = $result['metrics'];
            $this->info("\n--- Métricas da API ---");
            $this->info("Modelo Utilizado: {$metrics['model']} ({$metrics['model_name']})");
            $this->info("Tempo de Resposta: {$metrics['duration']} segundos");
            $this->info("-----------------------\n");
            
            $this->info("Análise concluída com sucesso!");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Exibe os resultados da análise.
     *
     * @param array $results Resultados formatados
     * @return void
     */
    private function displayResults(array $results)
    {
        if (empty($results)) {
            $this->info("Nenhum resultado para exibir.");
            return;
        }
        
        // Preparar os dados para a tabela
        $tableData = [];
        foreach ($results as $result) {
            $tableData[] = [
                'ID' => $result['id'],
                'Termo' => $result['search_term'],
                'Impressões' => $result['impressions'],
                'Cliques' => $result['clicks'],
                'Custo' => $result['cost_formatted'],
                'Negativar?' => $result['should_negate'] ? 'SIM' : 'NÃO',
                'Racional' => $result['rationale']
            ];
        }
        
        // Exibir a tabela
        $this->info("\nResultados da Análise:");
        $this->table(
            ['ID', 'Termo', 'Impressões', 'Cliques', 'Custo', 'Negativar?', 'Racional'],
            $tableData
        );
        
        // Contar quantos termos são recomendados para negativação
        $negativeCount = count(array_filter($tableData, function ($row) {
            return $row['Negativar?'] === 'SIM';
        }));
        
        $this->info("\n{$negativeCount} de " . count($tableData) . " termos são recomendados para negativação.");
    }
}
