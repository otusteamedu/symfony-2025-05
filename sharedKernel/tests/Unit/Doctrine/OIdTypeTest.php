<?php

declare(strict_types=1);

namespace bravik\Shared\Tests\Unit\Doctrine;

use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use InvalidArgumentException;
use bravik\Shared\Domain\Model\OId;
use bravik\Shared\Infrastructure\Doctrine\Types\OIdType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(OIdType::class)]
final class OIdTypeTest extends TestCase
{
    public function testCanBePersisted(): void
    {
        $type = OId::next();

        self::assertSame(
            $type->toBinary(),
            self::getType()->convertToDatabaseValue($type, $this->getPlatform())
        );
    }

    public function testCanBePersistedAsNull(): void
    {
        self::assertNull(
            self::getType()->convertToDatabaseValue(null, $this->getPlatform())
        );
    }

    #[DataProvider('cannotBePersistedAsOtherTypesProvider')]
    public function testCannotBePersistedAsOtherTypes(string|int|float|bool $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        self::getType()->convertToDatabaseValue($value, $this->getPlatform());
    }

    public static function cannotBePersistedAsOtherTypesProvider(): array
    {
        return [
            [1],
            [-1],
            [0],
            [0.53],
            ['11'],
            [true],
            ['017b9eac-a89a-74f4-321c-7dfd355997d9'], // ULID in RFC4122
        ];
    }

    public function testCanBeHydrated(): void
    {
        $id = OId::next();

        $type = self::getType()->convertToPHPValue(
            $id->toBinary(),
            $this->getPlatform()
        );

        self::assertInstanceOf(OId::class, $type);
        self::assertTrue($id->isEqual($type));
    }

    public function testCanBeHydratedAsNull(): void
    {
        $type = self::getType()->convertToPHPValue(
            null,
            $this->getPlatform()
        );

        self::assertNull($type);
    }

    #[DataProvider('cannotBeHydratedAsOtherTypesProvider')]
    public function testCannotBeHydratedAsOtherTypes(string|int|float|bool $value): void
    {
        $this->expectException(ConversionException::class);

        self::getType()->convertToPHPValue($value, $this->getPlatform());
    }

    public static function cannotBeHydratedAsOtherTypesProvider(): array
    {
        return [
            [1],
            [-1],
            [0],
            [0.53],
            ['11'],
            [true],
            ['017b9eac-a89a-74f4-321c-7dfd355997d9'], // ULID in RFC4122
        ];
    }

    public function testName(): void
    {
        self::assertSame(
            OIdType::NAME,
            self::getType()->getName(),
            'Apparently name was changed. Make sure you updated all entities used type "' . OIdType::NAME . '"'
        );
    }

    public function testRequiresCommentHint(): void
    {
        self::assertTrue(
            self::getType()->requiresSQLCommentHint($this->getPlatform())
        );
    }

    public function testMySqlColumnStructure(): void
    {
        self::assertSame(
            'BINARY(16)',
            self::getType()->getSQLDeclaration([], $this->getPlatform())
        );
    }

    public function testPostgresColumnStructure(): void
    {
        self::assertSame(
            'UUID',
            self::getType()->getSQLDeclaration([], new PostgreSQLPlatform())
        );
    }

    private static function getType(): OIdType
    {
        return new OIdType();
    }

    private function getPlatform(): MySQL80Platform
    {
        return new MySQL80Platform();
    }
}
