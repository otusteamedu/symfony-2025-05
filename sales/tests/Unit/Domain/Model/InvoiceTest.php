<?php

declare(strict_types=1);

namespace Domain\Model;

use bravik\Sales\Domain\Exceptions\InvoiceIsNotAwaitingPaymentException;
use bravik\Sales\Domain\Model\Currency;
use bravik\Sales\Domain\Model\Invoice\Invoice;
use bravik\Sales\Domain\Model\Invoice\Status;
use bravik\Sales\Domain\Model\Price;
use bravik\Shared\Domain\Model\OId;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Invoice::class)]
class InvoiceTest extends TestCase
{
    private OId $customerId;
    private OId $subscriptionId;
    private Price $amount;
    private DateTimeImmutable $dueDate;
    private Invoice $invoice;

    protected function setUp(): void
    {
        $this->customerId = OId::next();
        $this->subscriptionId = OId::next();
        $this->amount = new Price(1000, Currency::USD);
        $this->dueDate = new DateTimeImmutable('+30 days');
        $this->invoice = Invoice::create(
            $this->customerId,
            $this->subscriptionId,
            $this->amount,
            $this->dueDate
        );
    }

    public function testCreateInvoice(): void
    {
        self::assertInstanceOf(Invoice::class, $this->invoice);
        self::assertTrue($this->customerId->isEqual($this->invoice->getCustomerId()));
        self::assertTrue($this->subscriptionId->isEqual($this->invoice->getSubscriptionId()));
        self::assertEquals($this->amount, $this->invoice->getPrice());
        self::assertEquals($this->dueDate, $this->invoice->getDueDate());
        self::assertEquals(Status::PENDING, $this->invoice->getStatus());
        self::assertNull($this->invoice->getPaidAt());
    }

    public function testPayInvoice(): void
    {
        $transactionId = 'transaction-123';
        $this->invoice->pay($transactionId);
        self::assertEquals(Status::PAID, $this->invoice->getStatus());
        self::assertEquals($transactionId, $this->invoice->getTransactionId());
        self::assertNotNull($this->invoice->getPaidAt());
        self::assertTrue($this->invoice->isPaid());
    }

    public function testCannotPayPaidInvoice(): void
    {
        $this->expectException(DomainException::class);
        $this->invoice->pay('123');
        $this->invoice->pay('123');
    }

    public function testExpireInvoice(): void
    {
        $this->invoice->expire();
        self::assertEquals(Status::EXPIRED, $this->invoice->getStatus());
        self::assertTrue($this->invoice->isExpired());
    }

    public function testCannotExpirePaidInvoice(): void
    {
        $this->expectException(InvoiceIsNotAwaitingPaymentException::class);
        $this->invoice->pay('transaction-123');
        $this->invoice->expire();
    }
}