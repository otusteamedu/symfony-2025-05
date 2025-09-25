<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Commands\Invoices\Create;

use bravik\Shared\Domain\Model\OId;
use DateTimeImmutable;

final readonly class Command
{
    public function __construct(
        public OId $subscriptionId,
        public DateTimeImmutable $dueDate,
    ) {
    }
}