<?php

declare(strict_types=1);

namespace bravik\Shared\Infrastructure\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use bravik\Shared\Domain\Model\Email;
use Webmozart\Assert\Assert;

class EmailType extends StringType
{
    public const NAME = 'shared__email';

    /**
     * @param mixed|Email $value
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        Assert::isInstanceOf($value, Email::class);

        return $value->toString();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Email
    {
        if ($value === null) {
            return null;
        }

        Assert::stringNotEmpty($value, 'Email type is represented by a varchar database type. Not-empty string or null values allowed');

        return new Email($value);
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
