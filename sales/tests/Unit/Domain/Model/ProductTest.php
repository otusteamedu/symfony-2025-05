<?php

declare(strict_types=1);

namespace bravik\Sales\Tests\Unit\Domain\Model;

use bravik\Sales\Domain\Model\Product;
use bravik\Sales\Domain\Model\Price;
use bravik\Sales\Domain\Model\Currency;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Product::class)]
class ProductTest extends TestCase
{
    private Product $product;
    private Price $price;
    private Currency $currency;

    protected function setUp(): void
    {
        $this->currency = Currency::USD;
        $this->price = new Price(1000, $this->currency);
        $this->product = Product::create('Test Product', $this->price);
    }

    public function testCreateProductGeneratesId(): void
    {
        self::assertNotNull($this->product->getId());
    }

    public function testProductHasCorrectName(): void
    {
        self::assertSame('Test Product', $this->product->getName());
    }

    public function testProductHasCorrectPrice(): void
    {
        self::assertTrue($this->product->getPrice()->isEqual($this->price));
    }

    public function testUpdateName(): void
    {
        $this->product->updateName('New Name');
        self::assertSame('New Name', $this->product->getName());
    }

    public function testUpdatePrice(): void
    {
        $newPrice = new Price(2000, Currency::EUR);
        $this->product->updatePrice($newPrice);
        self::assertTrue($this->product->getPrice()->isEqual($newPrice));
    }
}