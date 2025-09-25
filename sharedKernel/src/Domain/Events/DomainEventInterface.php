<?php

declare(strict_types=1);

namespace bravik\Shared\Domain\Events;

use bravik\Shared\Domain\Model\OId;
use DateTimeImmutable;

interface DomainEventInterface
{
    public function getId(): OId;

    public function getAggregateId(): OId;

    public function getTimestamp(): DateTimeImmutable;

    /**
     * События отправленные в Message broker будут сериализованы в JSON.
     * Тип события определяется по полю type в этом JSON.
     */
    public function getType(): string;
}
