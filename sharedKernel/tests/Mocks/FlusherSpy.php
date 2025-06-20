<?php

declare(strict_types=1);

namespace bravik\Shared\Tests\Mocks;

use bravik\Shared\Application\FlusherInterface;

final class FlusherSpy implements FlusherInterface
{
    public array $flushed = [];

    public function flush(?string $className = null): void
    {
        $this->flushed[] = $className;
    }
}
