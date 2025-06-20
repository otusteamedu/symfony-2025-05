<?php

declare(strict_types=1);

namespace bravik\Sales\Application\Repositories;

use bravik\Sales\Domain\Exceptions\NotFoundException;
use bravik\Sales\Domain\Model\Invoice\Invoice;
use bravik\Shared\Domain\Model\OId;

interface InvoicesRepositoryInterface
{
    /**
     * @throws NotFoundException
     */
    public function get(OId $id): Invoice;

    public function find(OId $id): ?Invoice;

    public function add(Invoice $invoice): void;

    public function findLatestPendingInvoiceForSubscription(OId $subscriptionId): ?Invoice;
}