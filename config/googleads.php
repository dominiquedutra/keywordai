<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Negative Keyword List ID
    |--------------------------------------------------------------------------
    |
    | This value specifies the default Google Ads Shared Set ID for the 
    | global negative keyword list used when adding negative keywords 
    | through the application interface, unless otherwise specified.
    |
    */

    'default_negative_list_id' => env('GOOGLE_ADS_DEFAULT_NEGATIVE_LIST_ID'),

    /*
    |--------------------------------------------------------------------------
    | Absolute Start Date for Search Term Stats
    |--------------------------------------------------------------------------
    |
    | This value specifies the absolute start date used when fetching historical
    | statistics for search terms. This ensures we capture the complete history
    | of a search term's performance since the beginning of the account.
    |
    */

    'absolute_start_date' => env('GOOGLE_ADS_ABSOLUTE_START_DATE', '2000-01-01'),

];
