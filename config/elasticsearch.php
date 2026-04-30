<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Elasticsearch integration
    |--------------------------------------------------------------------------
    | When false, jobs no-op and API returns structured "unavailable" responses.
    */
    'enabled' => env('ELASTICSEARCH_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Cluster hosts (e.g. http://127.0.0.1:9200)
    |--------------------------------------------------------------------------
    */
    'hosts' => array_values(array_filter(array_map('trim', explode(',', (string) env('ELASTICSEARCH_HOSTS', 'http://127.0.0.1:9200'))))),

    /*
    |--------------------------------------------------------------------------
    | Basic auth (optional)
    |--------------------------------------------------------------------------
    */
    'username' => env('ELASTICSEARCH_USERNAME'),
    'password' => env('ELASTICSEARCH_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Main transactions index (income + expense documents)
    |--------------------------------------------------------------------------
    */
    'index_transactions' => env('ELASTICSEARCH_INDEX_TRANSACTIONS', 'famledger_transactions'),

    /*
    |--------------------------------------------------------------------------
    | API defaults
    |--------------------------------------------------------------------------
    */
    'search_size_default' => (int) env('ELASTICSEARCH_SEARCH_SIZE', 15),
    'search_size_max' => (int) env('ELASTICSEARCH_SEARCH_SIZE_MAX', 50),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL for identical search queries (seconds)
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => (int) env('ELASTICSEARCH_CACHE_TTL', 60),
];
