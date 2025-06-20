<?php

declare(strict_types=1);

namespace bravik\Sales\Domain\Model\Invoice;

/**
 * Тоже Value Object в виде enum
 */
enum Status: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case EXPIRED = 'expired';
}