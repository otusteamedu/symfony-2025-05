<?php

declare(strict_types=1);

namespace bravik\Shared\Infrastructure\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySQL80Platform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;
use bravik\Shared\Domain\Model\OId;
use Webmozart\Assert\Assert;

final class OIdType extends Type
{
    public const NAME = 'shared__oid';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        Assert::isInstanceOf($value, OId::class);

        /** @var OId $value */

        return $value->toBinary();
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?OId
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new ConversionException(
                'Could not convert database value "' . $value . '" to Doctrine Type ' . $this->getName()
            );
        }

        try {
            return OId::fromString($value);
        } catch (InvalidArgumentException $e) {
            throw new ConversionException(
                'Could not convert database value "' . $value . '" to Doctrine Type ' . $this->getName()
            );
        }
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        if ($platform instanceof PostgreSQLPlatform) {
            return $platform->getGuidTypeDeclarationSQL($column);
        }

        if ($platform instanceof MySQL80Platform) {
            return $platform->getBinaryTypeDeclarationSQL([
                'length' => 16,
                'fixed'  => true,
            ]);
        }

        throw new \RuntimeException('Unsupported platform');
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
