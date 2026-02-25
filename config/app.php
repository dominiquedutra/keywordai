<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Google Ads PHP INI Path
    |--------------------------------------------------------------------------
    |
    | Specifies the path to the google_ads_php.ini file used by the
    | Google Ads API client library.
    |
    */

    'google_ads_php_path' => config_path('google_ads_php.ini'),
    
    /*
    |--------------------------------------------------------------------------
    | Google Ads Client Customer ID
    |--------------------------------------------------------------------------
    |
    | Specifies the client customer ID (not the MCC account) to be used for
    | Google Ads API queries. This is the account where campaigns will be fetched.
    |
    */

    'client_customer_id' => '6822168268', // Fixed comma

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist Configuration
    |--------------------------------------------------------------------------
    |
    | Controls the IP-based access restriction.
    | 'force_ip_whitelist': Set to true via .env to enable the check.
    | 'whitelisted_ip_addresses': Comma-separated string of allowed IPs from .env.
    |
    */

    'force_ip_whitelist' => env('FORCE_IP_WHITELIST', false),
    'whitelisted_ip_addresses' => env('WHITELISTED_IP_ADDRESSES', '127.0.0.1,::1'),

    /*
    |--------------------------------------------------------------------------
    | Google Chat Notification Control
    |--------------------------------------------------------------------------
    |
    | Controls whether Google Chat notifications are sent for new search terms.
    | When set to false, notifications will be suppressed but the notified_at
    | field in the database will still be updated. This is useful during
    | initial data synchronization to prevent notification spam.
    |
    */

    'send_google_chat_notifications' => env('SEND_GOOGLE_CHAT_NOTIFICATIONS', true),

    /*
    |--------------------------------------------------------------------------
    | Account Creation Control
    |--------------------------------------------------------------------------
    |
    | Controls whether new user accounts can be created via the registration
    | form. Set this to false via the .env file to disable registration.
    |
    */

    'allow_account_creation' => env('ALLOW_ACCOUNT_CREATION', true),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
