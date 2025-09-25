<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Commands\Subscriptions\SubmitInvoice;

use bravik\Sales\Application\Repositories\InvoicesRepositoryInterface;
use bravik\Sales\Application\Repositories\SubscriptionsRepositoryInterface;
use bravik\Shared\Application\EventDispatcherInterface;
use bravik\Shared\Application\FlusherInterface;

final readonly class Handler
{
    public function __construct(
        private InvoicesRepositoryInterface $invoices,
        private SubscriptionsRepositoryInterface $subscriptionsRepository,
        private FlusherInterface $flusher,
        private EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * @throws InvoiceIsNotPaidException
     * @throws InvalidSubscriptionStateException
     */
    public function handle(Command $command): void
    {
        $invoice = $this->invoices->get($command->invoiceId);
        if (!$invoice->isPaid()) {
            throw new InvoiceIsNotPaidException();
        }

        $subscription = $this->subscriptionsRepository->get($invoice->getSubscriptionId());

        if ($subscription->isPending()) {
            $subscription->activate();
        } elseif ($subscription->isActive()) {
            $subscription->renew();
        } else {
            throw new InvalidSubscriptionStateException();
        }

        $this->flusher->flush();
        $this->dispatcher->dispatch(...$subscription->releaseEvents());
    }
}