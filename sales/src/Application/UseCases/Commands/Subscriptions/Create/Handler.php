<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Commands\Subscriptions\Create;

use bravik\Sales\Application\Repositories\ProductsRepositoryInterface;
use bravik\Sales\Application\Repositories\SubscriptionsRepositoryInterface;
use bravik\Sales\Domain\Model\Subscription\Subscription;
use bravik\Shared\Application\EventDispatcherInterface;
use bravik\Shared\Application\FlusherInterface;

final class Handler
{
    public function __construct(
        private readonly SubscriptionsRepositoryInterface $subscriptions,
        private readonly ProductsRepositoryInterface $productsRepository,
        private readonly FlusherInterface $flusher,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @throws CustomerAlreadyHasActiveSubscriptionException
     */
    public function handle(Command $command): string
    {
        if ($this->subscriptions->hasActiveSubscription($command->customerId)) {
            throw new CustomerAlreadyHasActiveSubscriptionException();
        }

        $product = $this->productsRepository->get($command->productId);
        $endDate = $command->startDate->modify('+1 month');

        $subscription = Subscription::create(
            $command->customerId,
            $command->productId,
            $product->getPrice(),
            $command->startDate,
            $endDate
        );

        $this->subscriptions->add($subscription);
        $this->flusher->flush();

        $this->dispatcher->dispatch(...$subscription->releaseEvents());

        return $subscription->getId()->toString();
    }
}