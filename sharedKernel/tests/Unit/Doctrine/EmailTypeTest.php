<?php

declare(strict_types=1);

namespace bravik\Shared\Tests\Unit\Doctrine;

use Doctrine\DBAL\Platforms\MySQL80Platform;
use InvalidArgumentException;
use bravik\Shared\Domain\Model\Email;
use bravik\Shared\Infrastructure\Doctrine\Types\EmailType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailType::class)]
final class EmailTypeTest extends TestCase
{
    #[DataProvider('canBePersistedProvider')]
    public function testCanBePersisted(string $testValue, string $expected): void
    {
        $type = new Email($testValue);

        self::assertSame(
            $expected,
            self::getType()->convertToDatabaseValue($type, self::getPlatform())
        );
    }

    public static function canBePersistedProvider(): array
    {
        return [
            [
                'somethin@email.tld', 'somethin@email.tld',
            ],
            [
                'soMEthin2@eMAil.tld', 'somethin2@email.tld',
            ],
        ];
    }

    public function testCanBePersistedAsNull(): void
    {
        self::assertNull(
            self::getType()->convertToDatabaseValue(null, self::getPlatform())
        );
    }

    public function testCanBeHydrated(): void
    {
        $type = self::getType()->convertToPHPValue(
            'somethin2@email.tld',
            self::getPlatform()
        );

        self::assertInstanceOf(Email::class, $type);
        self::assertSame('somethin2@email.tld', $type->toString());
    }

    public function testCanBeHydratedAsNull(): void
    {
        $type = self::getType()->convertToPHPValue(
            null,
            self::getPlatform()
        );

        self::assertNull($type);
    }

    #[DataProvider('wrongDbTypedValueProvider')]
    public function testCannotBeHydratedWithTypesOtherIntOrNull(string|int|float $value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email type is represented by a varchar database type. Not-empty string or null values allowed');

        self::getType()->convertToPHPValue(
            $value,
            self::getPlatform()
        );
    }

    public function testName(): void
    {
        self::assertSame('shared__email', self::getType()->getName(), 'Apparently name was changed. Make sure you updated all entities used type `shared__email`');
    }

    public static function wrongDbTypedValueProvider(): array
    {
        return [
            [-1],
            [0],
            [''],
            [1.2],
            [1.0],
        ];
    }

    private static function getType(): EmailType
    {
        return new EmailType();
    }

    private function getPlatform(): MySQL80Platform
    {
        return new MySQL80Platform();
    }
}
