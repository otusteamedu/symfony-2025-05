<?php

declare(strict_types=1);

namespace bravik\Shared\Tests\Unit\Domain\Model;

use InvalidArgumentException;
use bravik\Shared\Domain\Model\Name;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Name::class)]
class NameTest extends TestCase
{
    public function testValid(): void
    {
        $first = "Vladimir";
        $last  = "Putin";

        $name = new Name($first, $last);

        self::assertEquals($first, $name->getFirst());
        self::assertEquals($last, $name->getLast());
    }

    public function testFull(): void
    {
        $first = "Vladimir";
        $last  = "Putin";

        $name = new Name($first, $last);

        self::assertEquals($first . " " . $last, $name->getFull());
    }

    public function testEqual(): void
    {
        $first = "Vladimir";
        $last  = "Putin";

        $name      = new Name($first, $last);
        $equalName = new Name($first, $last);

        $notEqualName = new Name("pavel", "volya");

        self::assertTrue($name->isEqual($equalName));
        self::assertNotTrue($name->isEqual($notEqualName));
    }

    public function testEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Name('', '');
    }

    public function testToString(): void
    {
        self::assertEquals('Vladimir Putin', (string) (new Name('Vladimir', 'Putin')));
    }
}
