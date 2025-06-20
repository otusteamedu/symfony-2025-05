<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Commands\Invoices\Create;

use bravik\Sales\Application\Repositories\InvoicesRepositoryInterface;
use bravik\Sales\Application\Repositories\SubscriptionsRepositoryInterface;
use bravik\Sales\Domain\Model\Invoice\Invoice;
use bravik\Shared\Application\EventDispatcherInterface;
use bravik\Shared\Application\FlusherInterface;

final class Handler
{
    public function __construct(
        private readonly SubscriptionsRepositoryInterface $subscriptionsRepository,
        private readonly InvoicesRepositoryInterface $invoices,
        private readonly FlusherInterface $flusher,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    public function handle(Command $command): void
    {
        $subscription = $this->subscriptionsRepository->get($command->subscriptionId);

        $invoice = Invoice::create(
            $subscription->getCustomerId(),
            $subscription->getId(),
            $subscription->getPrice(),
            $command->dueDate,
        );

        $this->invoices->add($invoice);

        $this->flusher->flush();

        $this->dispatcher->dispatch(...$invoice->releaseEvents());
    }
}