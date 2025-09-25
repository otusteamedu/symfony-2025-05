<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Queries\Invoices\GeneratePaymentLink;

use bravik\Sales\Application\Repositories\InvoicesRepositoryInterface;
use bravik\Sales\Infrastructure\PaymentGateway;
use bravik\Shared\Domain\Model\OId;

final readonly class Fetcher
{
    public function __construct(
        public InvoicesRepositoryInterface $invoicesRepository,
        public PaymentGateway $paymentGateway,
    ) {
    }

    /**
     * @throws NoInvoicesAvailableException
     */
    public function fetch(Query $query): Result
    {
        $subscriptionId = OId::fromString($query->subscriptionId);
        $invoice = $this->invoicesRepository->findLatestPendingInvoiceForSubscription($subscriptionId);

        if (!$invoice) {
            throw new NoInvoicesAvailableException();
        }

        return new Result(
            $this->paymentGateway->getPaymentLink($invoice->getId()->toString())
        );
    }
}