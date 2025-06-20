<?php

declare(strict_types=1);

namespace bravik\Shared\Tests\Mocks;

use bravik\Shared\Application\EventDispatcherInterface;
use bravik\Shared\Domain\Events\DomainEventInterface;

class EventDispatcherSpy implements EventDispatcherInterface
{
    /** @var DomainEventInterface[] */
    public array $recordedEvents = [];

    public function __construct(
        private ?EventDispatcherInterface $realEventDispatcher = null
    ) {
    }

    public function dispatch(DomainEventInterface ...$events): void
    {
        foreach ($events as $event) {
            $this->recordedEvents[] = $event;

            if ($this->realEventDispatcher !== null) {
                $this->realEventDispatcher->dispatch($event);
            }
        }
    }

    /**
     * @return DomainEventInterface[]
     */
    public function getRecordedEvents(): array
    {
        return $this->recordedEvents;
    }
}
