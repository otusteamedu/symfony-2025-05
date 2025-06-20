<?php

declare(strict_types=1);

namespace bravik\Sales\Domain\Events;

use bravik\Shared\Domain\Events\AbstractDomainEvent;
use bravik\Shared\Domain\Model\OId;

class SubscriptionExpiredEvent extends AbstractDomainEvent
{
    public function __construct(
        OId $invoiceId,
    ) {
        parent::__construct($invoiceId);
    }
}