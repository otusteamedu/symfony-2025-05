<?php

declare(strict_types=1);

namespace bravik\Shared\Tests\Unit\Domain\Model;

use InvalidArgumentException;
use bravik\Shared\Domain\Model\OId;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass(OId::class)]
class OIdTest extends TestCase
{
    public function testNext(): void
    {
        OId::next();
        self::expectNotToPerformAssertions();
    }

    public function testRFC4122Conversion(): void
    {
        $str = '1b9dd929-b9ee-4a1e-b0ea-5050df2353be';

        // Create from RFC4122
        $id = OId::fromString($str);

        // Convert back to RFC4122
        self::assertEquals($str, $id->toRfc4122());
        self::assertEquals($str, $id->toString());
        self::assertEquals($str, (string) $id);
    }

    public function testFromULIDinRFC4122(): void
    {
        $str = '017b9eac-a89a-74f4-321c-7dfd355997d9';

        $this->expectException(InvalidArgumentException::class);
        OId::fromString($str);
    }

    public function testIsEqual(): void
    {
        $str        = '1b9dd929-b9ee-4a1e-b0ea-5050df2353be';
        $id         = OId::fromString($str);
        $equalId    = OId::fromString($str);
        $notEqualId = OId::next();

        self::assertTrue($id->isEqual($equalId));
        self::assertNotTrue($id->isEqual($notEqualId));
    }

    public function testToAndFromBinary(): void
    {
        $str       = '1b9dd929-b9ee-4a1e-b0ea-5050df2353be';
        $id        = OId::fromString($str);
        $anotherId = OId::fromBinary($id->toBinary());

        self::assertTrue($id->isEqual($anotherId));
    }
}
