<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Commands\Subscriptions\Create;

use DomainException;

final class CustomerAlreadyHasActiveSubscriptionException extends DomainException
{
}