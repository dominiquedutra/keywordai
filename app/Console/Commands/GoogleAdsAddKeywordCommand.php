<?php

namespace App\Console\Commands;

use App\Jobs\AddKeywordToAdGroupJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class GoogleAdsAddKeywordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'googleads:add-keyword 
                            {term : The search term to add as a keyword} 
                            {ad-group-id : The ID of the ad group} 
                            {match-type : The match type (exact, phrase, broad)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds a search term as a keyword to a specific ad group by dispatching a job.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $term = $this->argument('term');
        $adGroupId = $this->argument('ad-group-id');
        $matchType = strtolower($this->argument('match-type')); // Convert to lowercase for validation

        // Validate Ad Group ID
        if (!is_numeric($adGroupId) || $adGroupId <= 0) {
            $this->error('Invalid Ad Group ID provided. It must be a positive integer.');
            return Command::FAILURE;
        }
        $adGroupId = (int) $adGroupId; // Cast to integer after validation

        // Validate Match Type
        $validMatchTypes = ['exact', 'phrase', 'broad'];
        if (!in_array($matchType, $validMatchTypes)) {
            $this->error("Invalid match type '{$matchType}'. Must be one of: " . implode(', ', $validMatchTypes));
            return Command::FAILURE;
        }

        // Get Client Customer ID from config
        $clientCustomerId = config('app.client_customer_id');
        if (empty($clientCustomerId)) {
            $iniPath = config('app.google_ads_php_path');
            if (file_exists($iniPath)) {
                $iniConfig = parse_ini_file($iniPath, true);
                $clientCustomerId = $iniConfig['GOOGLE_ADS']['clientCustomerId'] ?? null;
            }
        }
        if (empty($clientCustomerId)) {
            $this->error('Client Customer ID is missing in configuration (config/app.php or google_ads_php.ini).');
            return Command::FAILURE;
        }

        try {
            $this->info("Dispatching job to add keyword '{$term}' ({$matchType}) to Ad Group ID {$adGroupId}...");

            AddKeywordToAdGroupJob::dispatch(
                $term,
                $adGroupId,
                $matchType, // Pass validated lowercase type, job will uppercase
                $clientCustomerId
            );

            $this->info("Job dispatched successfully. Check queue worker logs for processing details.");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to dispatch job: {$e->getMessage()}");
            Log::error("Error dispatching AddKeywordToAdGroupJob from command:", [
                'term' => $term,
                'ad_group_id' => $adGroupId,
                'match_type' => $matchType,
                'error' => $e->getMessage(),
            ]);
            return Command::FAILURE;
        }
    }
}
