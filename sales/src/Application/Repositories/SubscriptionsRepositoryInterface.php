<?php

declare(strict_types=1);

namespace bravik\Sales\Application\Repositories;

use bravik\Sales\Domain\Exceptions\NotFoundException;
use bravik\Sales\Domain\Model\Subscription\Subscription;
use bravik\Shared\Domain\Model\OId;

interface SubscriptionsRepositoryInterface
{

    /**
     * @throws NotFoundException
     */
    public function get(OId $id): Subscription;

    public function find(OId $id): ?Subscription;

    public function add(Subscription $subscription): void;

    public function hasActiveSubscription(OId $customerId): bool;
}