<?php

declare(strict_types=1);

namespace bravik\Sales\Domain\Model\Subscription;

use bravik\Sales\Domain\Events\SubscriptionActivatedEvent;
use bravik\Sales\Domain\Events\SubscriptionCancelledEvent;
use bravik\Sales\Domain\Events\SubscriptionCreatedEvent;
use bravik\Sales\Domain\Events\SubscriptionExpiredEvent;
use bravik\Sales\Domain\Events\SubscriptionRenewedEvent;
use bravik\Sales\Domain\Exceptions\SubscriptionIsNotActiveException;
use bravik\Sales\Domain\Exceptions\SubscriptionIsNotPendingException;
use bravik\Sales\Domain\Exceptions\SubscriptionIsNotReadyForRenewalException;
use bravik\Sales\Domain\Model\Price;
use bravik\Shared\Domain\Events\EventsTrait;
use bravik\Shared\Domain\Model\AggregateRootInterface;
use bravik\Shared\Domain\Model\OId;
use DateTimeImmutable;
use DomainException;

/**
 * Агрегат "Подписка".
 * Подписка пользователя на продукт.
 * Обратите внимание, что все ссылки на другие агрегаты - это их идентификаторы, а не объекты.
 * У этого агрегата есть методы мутаторы с бизнес логикой.
 */
class Subscription implements AggregateRootInterface
{
    use EventsTrait;

    public function __construct(
        private OId $id,
        private OId $customerId,
        private OId $productId,
        private Price $price,
        private Status $status,
        private DateTimeImmutable $startDate,
        private ?DateTimeImmutable $endDate = null
    ) {
    }

    public static function create(
        OId $customerId,
        OId $productId,
        Price $price,
        DateTimeImmutable $startDate,
        ?DateTimeImmutable $endDate = null
    ): self {
        $subscription =  new self(
            OId::next(),
            $customerId,
            $productId,
            $price,
            Status::PENDING,
            $startDate,
            $endDate,
        );

        $subscription->recordEvent(
            new SubscriptionCreatedEvent(
                $subscription->getId(),
            )
        );

        return $subscription;
    }

    public function activate(): void
    {
        if ($this->status !== Status::PENDING) {
            throw new SubscriptionIsNotPendingException();
        }
        $this->status = Status::ACTIVE;

        $this->recordEvent(
            new SubscriptionActivatedEvent(
                $this->getId(),
            )
        );
    }

    public function cancel(): void
    {
        if ($this->status !== Status::ACTIVE) {
            throw new DomainException('Only active subscriptions can be cancelled');
        }
        $this->status = Status::CANCELLED;
        $this->endDate = new DateTimeImmutable();

        $this->recordEvent(
            new SubscriptionCancelledEvent(
                $this->getId(),
            )
        );
    }

    public function expire(): void
    {
        if ($this->status !== Status::ACTIVE) {
            throw new DomainException('Only active subscriptions can expire');
        }
        $this->status = Status::EXPIRED;
        $this->endDate = new DateTimeImmutable();

        $this->recordEvent(
            new SubscriptionExpiredEvent(
                $this->getId(),
            )
        );
    }

    public function renew(): void
    {
        if ($this->status !== Status::ACTIVE) {
            throw new SubscriptionIsNotActiveException();
        }

        if ($this->endDate === null) {
            throw new DomainException('Cannot renew subscription without end date');
        }

        $oneWeekFromNow = new DateTimeImmutable('+1 week');
        if ($this->endDate > $oneWeekFromNow) {
            throw new SubscriptionIsNotReadyForRenewalException();
        }

        $this->endDate = $this->endDate->modify('+1 month');

        $this->recordEvent(
            new SubscriptionRenewedEvent(
                $this->getId(),
            )
        );
    }

    public function getId(): OId
    {
        return $this->id;
    }

    public function getCustomerId(): OId
    {
        return $this->customerId;
    }

    public function getProductId(): OId
    {
        return $this->productId;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function isPending(): bool
    {
        return $this->status === Status::PENDING;
    }

    public function isActive(): bool
    {
        return $this->status === Status::ACTIVE;
    }

    public function isCancelled(): bool
    {
        return $this->status === Status::CANCELLED;
    }

    public function isExpired(): bool
    {
        return $this->status === Status::EXPIRED;
    }
}