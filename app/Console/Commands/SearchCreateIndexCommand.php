<?php

namespace App\Console\Commands;

use App\Services\Search\SearchService;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Illuminate\Console\Command;
use Throwable;

class SearchCreateIndexCommand extends Command
{
    protected $signature = 'search:index-create {--force : Delete existing index if present}';

    protected $description = 'Create the famledger_transactions Elasticsearch index (mapping + analyzers)';

    public function handle(SearchService $search): int
    {
        if (! $search->isEnabled()) {
            $this->warn('ELASTICSEARCH_ENABLED is false. Set it to true in .env after configuring hosts.');

            return self::FAILURE;
        }

        $hosts = config('elasticsearch.hosts', []);
        $this->line('Using hosts: '.implode(', ', $hosts));

        try {
            $client = $search->client();
            $index = $search->indexName();

            if ($client->indices()->exists(['index' => $index])->asBool()) {
                if (! $this->option('force')) {
                    $this->error('Index already exists. Use --force to delete and recreate.');

                    return self::FAILURE;
                }
                $client->indices()->delete(['index' => $index]);
                $this->info('Deleted existing index: '.$index);
            }

            $path = database_path('elasticsearch/famledger_transactions.json');
            $body = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

            $client->indices()->create([
                'index' => $index,
                'body' => $body,
            ]);

            $this->info('Created index: '.$index);

            return self::SUCCESS;
        } catch (NoNodeAvailableException $e) {
            $this->newLine();
            $this->error('Cannot reach Elasticsearch at '.implode(', ', $hosts));
            $this->line('Start a node on that URL, or set ELASTICSEARCH_HOSTS in .env to your cluster.');
            $this->line('Quick check (PowerShell):  Invoke-WebRequest -Uri http://127.0.0.1:9200 -UseBasicParsing');
            $this->line('Docker (single node):       docker run -d --name es -p 9200:9200 -e "discovery.type=single-node" -e "xpack.security.enabled=false" docker.elastic.co/elasticsearch/elasticsearch:8.11.0');

            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
