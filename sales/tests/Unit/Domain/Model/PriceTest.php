<?php

declare(strict_types=1);

namespace bravik\Sales\Tests\Unit\Domain\Model;

use bravik\Sales\Domain\Model\Price;
use bravik\Sales\Domain\Model\Currency;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Price::class)]
class PriceTest extends TestCase
{
    private Price $price;
    private Currency $currency;

    protected function setUp(): void
    {
        $this->currency = Currency::USD;
        $this->price = new Price(1000, $this->currency);
    }

    public function testCreatePrice(): void
    {
        self::assertSame(1000, $this->price->getAmount());
        self::assertEquals($this->price->getCurrency(), $this->currency);
    }

    public function testPriceEquality(): void
    {
        $samePrice = new Price(1000, Currency::USD);
        $differentPrice = new Price(2000, Currency::USD);

        self::assertTrue($this->price->isEqual($samePrice));
        self::assertFalse($this->price->isEqual($differentPrice));
    }

    public function testCannotCreateNegativePrice(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Price(-1000, $this->currency);
    }

    public function testCannotCreateZeroPrice(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Price(0, $this->currency);
    }
}