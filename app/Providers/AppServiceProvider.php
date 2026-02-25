<?php

namespace App\Providers;

use App\Models\SearchTerm;
use App\Observers\SearchTermObserver;
use Google\Ads\GoogleAds\Lib\V19\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Binds the Google Ads API client.
        $this->app->singleton('Google\Ads\GoogleAds\Lib\V19\GoogleAdsClient', function () {
            // Constructs a Google Ads API client configured from the properties file.
            return (new GoogleAdsClientBuilder())
                ->fromFile(config('app.google_ads_php_path'))
                ->withOAuth2Credential((new OAuth2TokenBuilder())
                    ->fromFile(config('app.google_ads_php_path'))
                    ->build())
                ->build();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Registrar o observer para o modelo SearchTerm
        SearchTerm::observe(SearchTermObserver::class);
    }
}
