<?php

namespace App\Services\Search;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

/**
 * Builds a singleton Elasticsearch PHP client from config.
 */
class ElasticsearchClientFactory
{
    public function make(): Client
    {
        $builder = ClientBuilder::create();

        $hosts = config('elasticsearch.hosts', []);
        if ($hosts !== []) {
            $builder->setHosts($hosts);
        }

        $user = config('elasticsearch.username');
        $password = config('elasticsearch.password');
        if (is_string($user) && $user !== '' && is_string($password) && $password !== '') {
            $builder->setBasicAuthentication($user, $password);
        }

        return $builder->build();
    }
}
