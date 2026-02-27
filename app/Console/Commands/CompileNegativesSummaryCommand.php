<?php

namespace App\Console\Commands;

use App\Services\NegativeKeywordsSummaryService;
use Illuminate\Console\Command;

class CompileNegativesSummaryCommand extends Command
{
    protected $signature = 'keywordai:compile-negatives-summary
                            {--force : Regenerate even if summary is not stale}
                            {--show-prompt : Display the meta-prompt before calling AI}';

    protected $description = 'Compiles negative keywords into an AI-synthesized summary profile for prompt optimization';

    public function __construct(private NegativeKeywordsSummaryService $summaryService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $force = $this->option('force');

        if (!$force && !$this->summaryService->isStale()) {
            $this->info('Summary is up-to-date (not stale). Use --force to regenerate.');
            return Command::SUCCESS;
        }

        if ($this->option('show-prompt')) {
            // Show what the meta-prompt looks like by building it manually
            $keywords = \App\Models\NegativeKeyword::all(['keyword', 'match_type', 'reason']);
            if ($keywords->isEmpty()) {
                $this->warn('No negative keywords found.');
                return Command::SUCCESS;
            }

            $this->info("Meta-prompt preview ({$keywords->count()} keywords):");
            $this->line(str_repeat('-', 80));

            // Build a preview of the keyword block
            $preview = $keywords->take(10)->map(fn ($kw) => "- \"{$kw->keyword}\" ({$kw->match_type}) — " . ($kw->reason ?: 'Sem motivo'))->implode("\n");
            $this->line($preview);
            if ($keywords->count() > 10) {
                $this->line("... and " . ($keywords->count() - 10) . " more keywords");
            }

            $this->line(str_repeat('-', 80));

            if (!$this->confirm('Proceed with AI synthesis?')) {
                $this->info('Cancelled.');
                return Command::SUCCESS;
            }
        }

        $model = setting('ai_summary_model', 'gemini');
        $modelName = setting('ai_summary_model_name', 'gemini-2.5-pro');
        $this->info("Generating summary using {$model}/{$modelName}...");

        $result = $this->summaryService->generate();

        if (!$result['success']) {
            $this->error("Failed: " . $result['error']);
            return Command::FAILURE;
        }

        $meta = $result['meta'];
        $this->info("Summary generated successfully!");
        $this->table(['Metric', 'Value'], [
            ['Keywords Analyzed', $meta['keyword_count']],
            ['Summary Size', number_format($meta['summary_size_bytes']) . ' bytes'],
            ['Duration', $meta['duration_seconds'] . 's'],
            ['Model', $meta['model_used']],
            ['Prompt Tokens', $meta['prompt_tokens'] ?? 'N/A'],
            ['Completion Tokens', $meta['completion_tokens'] ?? 'N/A'],
        ]);

        $this->newLine();
        $this->line($result['summary']);

        return Command::SUCCESS;
    }
}
