<?php

declare(strict_types=1);

namespace bravik\Sales\Tests\Mocks\Repositories;

use bravik\Sales\Domain\Exceptions\NotFoundException;
use bravik\Sales\Domain\Model\Currency;
use bravik\Sales\Domain\Model\Price;
use bravik\Sales\Domain\Model\Product;
use bravik\Sales\Application\Repositories\ProductsRepositoryInterface;
use bravik\Shared\Domain\Model\OId;

final class InMemoryProductsRepository implements \bravik\Sales\Application\Repositories\ProductsRepositoryInterface
{
    /** @var Product[] */
    public array $storage;

    public function __construct(array|null $storage = null)
    {
        if ($storage !== null) {
            $this->storage = $storage;
            return;
        }

        $this->storage = [
            new Product(
                id: OId::fromString(PrepopulatedTestObjects::PRODUCT_ID),
                name: 'Test Product',
                price: new Price(1000, Currency::USD)
            )
        ];
    }

    public function get(OId $id): Product
    {
        foreach ($this->storage as $product) {
            if ($product->getId()->isEqual($id)) {
                return $product;
            }
        }

        throw new NotFoundException();
    }
}