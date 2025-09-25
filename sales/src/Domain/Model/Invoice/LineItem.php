<?php

declare(strict_types=1);

namespace bravik\Sales\Domain\Model\Invoice;

/**
 * Это так же будет сущностью, но эта сущность - составляющая часть агрегата Invoice.
 * Все взаимодействие с LineItem происходит через Invoice.
 * У этой сущности не может быть репозитория.
 */
class LineItem
{
    public function __construct(
        private string $productId,
        private int $price,
        private int $quantity,
        private string $text,
    ) {
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getText(): string
    {
        return $this->text;
    }


}