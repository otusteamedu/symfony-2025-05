<?php

declare(strict_types=1);

namespace bravik\Shared\Tests\Unit\Domain\Model;

use bravik\Shared\Domain\Model\Email;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Email::class)]
class EmailTest extends TestCase
{
    public function testValid(): void
    {
        $validEmail = "example@email.com";
        $email      = new Email($validEmail);
        self::assertEquals($validEmail, $email->toString());
    }

    public function testEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('');
    }

    public function testInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Email('invalidemail');
    }

    public function testToString(): void
    {
        $str   = 'example@example.com';
        $email = new Email($str);
        self::assertEquals($str, (string) $email);
    }

    public function testEquality(): void
    {
        $email  = new Email('example@email.com');
        $email2 = new Email('example@email.com');
        $email3 = new Email('other@email.com');
        self::assertTrue($email->isEqual($email2));
        self::assertFalse($email->isEqual($email3));
    }
}
