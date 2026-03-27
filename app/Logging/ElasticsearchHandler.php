<?php

namespace App\Logging;

use Elastic\Elasticsearch\ClientBuilder;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Monolog\Level;

class ElasticsearchHandler
{
    public function __invoke(array $config): Logger
    {
        $client = ClientBuilder::create()
            ->setHosts([env('ELASTICSEARCH_HOST', 'http://localhost:9200')])
            ->build();

        $handler = new class($client) extends AbstractProcessingHandler {
            private $client;

            public function __construct($client)
            {
                parent::__construct(Level::Debug);
                $this->client = $client;
            }

            protected function write(LogRecord $record): void
            {
                file_put_contents('/tmp/es_debug.txt', 'write called' . PHP_EOL, FILE_APPEND);
                $this->client->index([
                    'index' => 'laravel-logs-' . date('Y.m.d'),
                    'body'  => [
                        '@timestamp' => $record->datetime->format('c'),
                        'message'    => $record->message,
                        'level'      => $record->level->value,
                        'level_name' => $record->level->name,
                        'channel'    => $record->channel,
                        'context'    => $record->context,
                        'extra'      => $record->extra,
                        'service'    => 'mikrotik-manager',
                    ],
                ]);
            }
        };

        return new Logger('elasticsearch', [$handler]);
    }
}