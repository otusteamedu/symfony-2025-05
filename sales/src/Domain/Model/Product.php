<?php

declare(strict_types=1);

namespace bravik\Sales\Domain\Model;

use bravik\Shared\Domain\Events\EventsTrait;
use bravik\Shared\Domain\Model\AggregateRootInterface;
use bravik\Shared\Domain\Model\OId;
use Webmozart\Assert\Assert;

/**
 * Агрегат "Продукт".
 * Продукт на который подписывается пользователь.
 * Не важен в контексте примера.
 */
class Product implements AggregateRootInterface
{
    use EventsTrait;

    private OId $id;
    private string $name;
    private Price $price;

    public function __construct(OId $id, string $name, Price $price)
    {
        Assert::stringNotEmpty($name);

        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
    }

    public static function create(string $name, Price $price): self
    {
        return new self(OId::next(), $name, $price);
    }

    public function getId(): OId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }


    public function updateName(string $name): void
    {
        $this->name = $name;
    }

    public function updatePrice(Price $price): void
    {
        $this->price = $price;
    }
}