<?php

declare(strict_types=1);

namespace bravik\Shared\Domain\Model;

interface AggregateRootInterface
{
    public function releaseEvents(): array;
}
