<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Commands\Invoices\Pay;

use bravik\Sales\Application\Repositories\InvoicesRepositoryInterface;
use bravik\Sales\Domain\Exceptions\InvoiceIsNotAwaitingPaymentException;
use bravik\Sales\Domain\Exceptions\NotFoundException;
use bravik\Shared\Application\EventDispatcherInterface;
use bravik\Shared\Application\FlusherInterface;

final class Handler
{
    public function __construct(
        private readonly InvoicesRepositoryInterface $invoicesRepository,
        private readonly FlusherInterface $flusher,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @throws NotFoundException
     * @throws InvoiceIsNotAwaitingPaymentException
     */
    public function handle(Command $command): void
    {
        $invoice = $this->invoicesRepository->get($command->invoiceId);

        $invoice->pay($command->transactionId);

        $this->flusher->flush();
        $this->dispatcher->dispatch(...$invoice->releaseEvents());
    }
}