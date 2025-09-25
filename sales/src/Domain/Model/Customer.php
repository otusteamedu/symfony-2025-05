<?php

declare(strict_types=1);

namespace bravik\Sales\Domain\Model;

use bravik\Shared\Domain\Events\EventsTrait;
use bravik\Shared\Domain\Model\AggregateRootInterface;
use bravik\Shared\Domain\Model\Email;
use bravik\Shared\Domain\Model\Name;
use bravik\Shared\Domain\Model\OId;

/**
 * Агрегат "Клиент".
 * Не важен в контексте примера.
 * В реальном проекте необходимо будет добавить событийную обвязку для синхронизации Customer с User из основного монолита.
 * Источник истины для Customer - это User из основного монолита.
 * Поэтому необходимо:
 *  1) Добавить в User:$oid - универсальный идентификатор (авто-инкрементные здесь не подходят
 *  2) Добавить генерацию событий при CRUD операциях с User
 *  3) Добавить слушатели для этих событий в модуле Sales для синхронизации Customer с User
 *
 * В рамках примера мы этим не занимаемся.
 */
class Customer implements AggregateRootInterface
{
    use EventsTrait;

    public OId $id;
    public Name $name;
    public Email $email;

    public function __construct(
        OId $id,
        Name $name,
        Email $email
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }

    public static function create(Name $name, Email $email): self
    {
        return new self(OId::next(), $name, $email);
    }

    public function getId(): OId
    {
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }
}