<?php

declare(strict_types=1);

namespace bravik\Sales\Domain\Exceptions;

use DomainException;

final class SubscriptionIsNotReadyForRenewalException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Subscription can only be renewed in the last week before expiration', 0, null);
    }

}