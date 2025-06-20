<?php

declare(strict_types=1);

namespace bravik\Shared\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final readonly class Name
{
    #[ORM\Column(name: 'first_name', type: 'string', length: 255)]
    private string $first;

    #[ORM\Column(name: 'last_name', type: 'string', length: 255)]
    private string $last;

    public function __construct(string $first, string $last)
    {
        Assert::stringNotEmpty($first);
        Assert::stringNotEmpty($last);

        $this->first = $first;
        $this->last  = $last;
    }

    public function getFirst(): string
    {
        return $this->first;
    }

    public function getLast(): string
    {
        return $this->last;
    }

    public function getFull(): string
    {
        return $this->first . ' ' . $this->last;
    }

    public function isEqual(self $anotherName): bool
    {
        return ($this->first === $anotherName->getFirst())
            && ($this->last === $anotherName->getLast());
    }

    public function __toString(): string
    {
        return $this->getFull();
    }
}
