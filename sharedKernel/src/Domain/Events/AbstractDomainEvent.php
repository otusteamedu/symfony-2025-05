<?php

declare(strict_types=1);

namespace bravik\Shared\Domain\Events;

use DateTimeImmutable;
use bravik\Shared\Domain\Model\OId;
use RuntimeException;

/**
 * @psalm-immutable
 *
 * @codeCoverageIgnore
 */
abstract class AbstractDomainEvent implements DomainEventInterface
{
    private OId $id;
    private DateTimeImmutable $timestamp;

    public function __construct(
        private readonly OId $aggregateId,
    ) {
        $this->id        = OId::next();
        $this->timestamp = new DateTimeImmutable();
    }

    public function getId(): OId
    {
        return $this->id;
    }

    public function getAggregateId(): OId
    {
        return $this->aggregateId;
    }

    public function getTimestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * Можно задавать явно тип для каждого события, но я автоматизирую это и генерирую тип
     * на основе конвенций принятых в компании для организации кода.
     *
     * Converts FQCN to system wide event type in format: bounded_context.domain_event_name
     *
     * All our namespaces follow the convention 'Vendor\BoundedContext\Domain\Events\SomeEvent'
     * So we extract context and event name from the namespace
     */
    public function getType(): string
    {
        $fqcn = explode('\\', static::class);

        /**
         * Context would be the second part of the namespace
         */
        $context = strtolower($fqcn[2]);

        /**
         * Event classname would be right after "Events" in the namespace
         */
        $position = array_search('Events', $fqcn);

        if ($position === false) {
            throw new RuntimeException('Unable to identify event');
        }
        $subset = array_slice($fqcn, $position + 1);
        $result = [];

        // Transform event name parts to snake case, remove "event" from the end
        foreach ($subset as $item) {
            preg_match_all('/[A-Z][^A-Z]*/', $item, $matches);

            if (end($matches[0]) === 'Event') {
                array_pop($matches[0]);
            }
            $newItem  = implode('_', array_map('strtolower', $matches[0]));
            $result[] = $newItem;
        }

        $eventId = implode('_', $result);

        return "$context.$eventId";
    }
}
