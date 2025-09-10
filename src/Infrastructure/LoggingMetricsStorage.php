<?php

declare(strict_types=1);

namespace App\Infrastructure;

use Psr\Log\LoggerInterface;
use StatsdBundle\Storage\MetricsStorageInterface;

class LoggingMetricsStorage implements MetricsStorageInterface
{

    public function __construct(
        private MetricsStorageInterface $metricsStorage,
        private LoggerInterface $logger,
    ) {
    }

    public function increment(string $key, ?float $sampleRate = null, ?array $tags = null): void
    {
        $this->logger->info('Incrementing metric', [
            'key' => $key,
            'sampleRate' => $sampleRate,
            'tags' => $tags,
        ]);
        $this->metricsStorage->increment($key, $sampleRate, $tags);
    }
}
