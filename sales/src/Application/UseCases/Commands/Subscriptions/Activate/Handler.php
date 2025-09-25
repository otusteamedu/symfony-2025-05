<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Commands\Subscriptions\Activate;

use bravik\Sales\Application\Repositories\SubscriptionsRepositoryInterface;
use bravik\Shared\Application\EventDispatcherInterface;
use bravik\Shared\Application\FlusherInterface;

final readonly class Handler
{
    public function __construct(
        private SubscriptionsRepositoryInterface $subscriptions,
        private FlusherInterface $flusher,
        private EventDispatcherInterface $dispatcher
    ) {
    }

    public function handle(Command $command): void
    {
        $subscription = $this->subscriptions->get($command->subscriptionId);

        $subscription->activate();

        $this->flusher->flush();

        $this->dispatcher->dispatch(...$subscription->releaseEvents());
    }
}