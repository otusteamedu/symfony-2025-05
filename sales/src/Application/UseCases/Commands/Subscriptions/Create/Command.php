<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Commands\Subscriptions\Create;

use bravik\Shared\Domain\Model\OId;
use DateTimeImmutable;

final readonly class Command
{
    public function __construct(
        public OId $customerId,
        public OId $productId,
        public DateTimeImmutable $startDate,
    ) {
    }
}