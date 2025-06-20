<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Commands\Invoices\Expire;

use bravik\Shared\Domain\Model\OId;

final readonly class Command
{
    public function __construct(
        public OId $invoiceId,
    ) {
    }
}