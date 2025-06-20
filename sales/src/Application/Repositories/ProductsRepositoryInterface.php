<?php

declare(strict_types=1);

namespace bravik\Sales\Application\Repositories;

use bravik\Sales\Domain\Exceptions\NotFoundException;
use bravik\Sales\Domain\Model\Product;
use bravik\Shared\Domain\Model\OId;

interface ProductsRepositoryInterface
{
    /**
     * @throws NotFoundException
     */
    public function get(OId $id): Product;
}