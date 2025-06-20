<?php

declare(strict_types=1);

namespace bravik\Sales\Tests\Mocks\Repositories;

use bravik\Sales\Domain\Exceptions\NotFoundException;
use bravik\Sales\Domain\Model\Currency;
use bravik\Sales\Domain\Model\Price;
use bravik\Sales\Domain\Model\Subscription\Status;
use bravik\Sales\Domain\Model\Subscription\Subscription;
use bravik\Sales\Application\Repositories\SubscriptionsRepositoryInterface;
use bravik\Shared\Domain\Model\OId;
use DateTimeImmutable;

final class InMemorySubscriptionsRepository implements SubscriptionsRepositoryInterface
{
    /** @var Subscription[] */
    public array $storage;

    /**
     * @param Subscription[]|null $storage
     */
    public function __construct(
        array|null $storage = null
    ) {
        if ($storage !== null) {
            $this->storage = $storage;
            return;
        }

        $subscription = new Subscription(
            id: OId::fromString(PrepopulatedTestObjects::SUBSCRIPTION_PENDING_ID),
            customerId: OId::fromString(PrepopulatedTestObjects::CUSTOMER_ID),
            productId: OId::fromString(PrepopulatedTestObjects::PRODUCT_ID),
            price: new Price(1000, Currency::USD),
            status: Status::PENDING,
            startDate: new DateTimeImmutable('2024-01-01'),
            endDate: new DateTimeImmutable('2024-12-31')
        );

        $this->storage = [
            // Example active subscription for testing
            $subscription
        ];
    }

    public function get(OId $id): Subscription
    {
        foreach ($this->storage as $subscription) {
            if ($subscription->getId()->isEqual($id)) {
                return $subscription;
            }
        }

        throw new NotFoundException();
    }

    public function find(OId $id): ?Subscription
    {
        try {
            return $this->get($id);
        } catch (NotFoundException) {
            return null;
        }
    }

    public function add(Subscription $subscription): void
    {
        $this->storage[] = $subscription;
    }

    public function hasActiveSubscription(OId $customerId): bool
    {
        foreach ($this->storage as $subscription) {
            if ($subscription->getCustomerId()->isEqual($customerId) && $subscription->isActive()) {
                return true;
            }
        }

        return false;
    }
}