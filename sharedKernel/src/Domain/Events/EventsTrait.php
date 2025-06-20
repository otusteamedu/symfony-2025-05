<?php

declare(strict_types=1);

namespace bravik\Shared\Domain\Events;

/**
 * Добавляет возможность агрегату записывать события, генерируемые в мутаторах.
 *
 * Трейты в целом - антипаттерн и зло.
 * Однако в данном конкретном случае удобно использовать именно трейт для добавления поведения агрегату.
 * Этот трейт должен использоваться только с Агрегатами, имплементирующими AggregateRootInterface
 * @see \bravik\Shared\Domain\Model\AggregateRootInterface
 */
trait EventsTrait
{
    /** @var DomainEventInterface[] */
    private array $recordedEvents = [];

    /**
     * @return DomainEventInterface[]
     */
    public function releaseEvents(): array
    {
        $events               = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    protected function recordEvent(DomainEventInterface $event): void
    {
        $this->recordedEvents[] = $event;
    }
}
