<?php

declare(strict_types=1);

namespace bravik\Sales\Domain\Model;

enum Currency: string
{
    case USD = 'USD';
    case EUR = 'EUR';
    case GBP = 'GBP';
}