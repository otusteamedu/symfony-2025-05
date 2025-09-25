<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Commands\Subscriptions\SubmitInvoice;

use DomainException;

final class InvalidSubscriptionStateException extends DomainException
{
}