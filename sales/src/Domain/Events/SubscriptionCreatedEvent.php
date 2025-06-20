<?php

declare(strict_types=1);

namespace bravik\Sales\Domain\Events;

use bravik\Shared\Domain\Events\AbstractDomainEvent;
use bravik\Shared\Domain\Model\OId;

class SubscriptionCreatedEvent extends AbstractDomainEvent
{
    public function __construct(
        OId $subscriptionId,
    ) {
        parent::__construct($subscriptionId);
    }
}