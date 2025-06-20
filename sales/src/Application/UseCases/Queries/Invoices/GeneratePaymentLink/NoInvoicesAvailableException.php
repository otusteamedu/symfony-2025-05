<?php

declare(strict_types=1);

namespace bravik\Sales\Application\UseCases\Queries\Invoices\GeneratePaymentLink;

use DomainException;

final class NoInvoicesAvailableException extends DomainException
{
}