<?php
    
    declare(strict_types=1);
    
    namespace bravik\Sales\Tests\Unit\Domain\Model;
    
    use bravik\Sales\Domain\Events\SubscriptionRenewedEvent;
    use bravik\Sales\Domain\Exceptions\SubscriptionIsNotActiveException;
    use bravik\Sales\Domain\Exceptions\SubscriptionIsNotReadyForRenewalException;
    use bravik\Sales\Domain\Model\Currency;
    use bravik\Sales\Domain\Model\Price;
    use bravik\Sales\Domain\Model\Subscription\Subscription;
    use bravik\Shared\Domain\Model\OId;
    use DateTimeImmutable;
    use DomainException;
    use PHPUnit\Framework\Attributes\CoversClass;
    use PHPUnit\Framework\TestCase;

    #[CoversClass(Subscription::class)]
    final class SubscriptionTest extends TestCase
    {
        public function testRenewSuccessfully(): void
        {
            $subscription = Subscription::create(
                customerId: OId::next(),
                productId: OId::next(),
                price: new Price(1000, Currency::USD),
                startDate: new DateTimeImmutable('2024-01-01'),
                endDate: new DateTimeImmutable('now +3 days')
            );
            
            $subscription->activate();
            $subscription->renew();
    
            self::assertTrue($subscription->isActive());
            
            $events = $subscription->releaseEvents();
            self::assertCount(3, $events);
            self::assertInstanceOf(SubscriptionRenewedEvent::class, $events[2]);
        }
    
        public function testCannotRenewInactiveSubscription(): void
        {
            $subscription = Subscription::create(
                customerId: OId::next(),
                productId: OId::next(),
                price: new Price(1000, Currency::USD),
                startDate: new DateTimeImmutable('2024-01-01'),
                endDate: new DateTimeImmutable('now +3 days')
            );
    
            $this->expectException(SubscriptionIsNotActiveException::class);
            $subscription->renew();
        }
    
        public function testCannotRenewSubscriptionWithoutEndDate(): void
        {
            $subscription = Subscription::create(
                customerId: OId::next(),
                productId: OId::next(),
                price: new Price(1000, Currency::USD),
                startDate: new DateTimeImmutable('2024-01-01')
            );
    
            $subscription->activate();
    
            $this->expectException(DomainException::class);
            $this->expectExceptionMessage('Cannot renew subscription without end date');
            $subscription->renew();
        }
    
        public function testCannotRenewSubscriptionTooEarly(): void
        {
            $subscription = Subscription::create(
                customerId: OId::next(),
                productId: OId::next(),
                price: new Price(1000, Currency::USD),
                startDate: new DateTimeImmutable('2024-01-01'),
                endDate: new DateTimeImmutable('now +2 weeks')
            );
    
            $subscription->activate();
    
            $this->expectException(SubscriptionIsNotReadyForRenewalException::class);
            $subscription->renew();
        }
    }