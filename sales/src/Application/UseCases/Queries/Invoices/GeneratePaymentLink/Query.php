<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Queries\Invoices\GeneratePaymentLink;

final readonly class Query
{
    public function __construct(
        public string $subscriptionId
    ) {
    }
}