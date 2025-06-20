<?php

declare(strict_types=1);

namespace bravik\Shared\Domain\Model;

use Webmozart\Assert\Assert;

final readonly class Email
{
    private string $value;

    public function __construct(string $value)
    {
        Assert::email($value);
        $this->value = mb_strtolower($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function isEqual(Email $email): bool
    {
        return $email->toString() === $this->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
