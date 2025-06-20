<?php

    declare(strict_types=1);

    namespace bravik\Sales\Tests\Unit\Application\UseCases\Commands\Invoices;

    use bravik\Sales\Application\UseCases\Commands\Invoices\Pay\Handler;
    use bravik\Sales\Domain\Events\InvoicePaidEvent;
    use bravik\Sales\Domain\Exceptions\InvoiceIsNotAwaitingPaymentException;
    use bravik\Sales\Domain\Exceptions\NotFoundException;
    use bravik\Sales\Tests\Mocks\Repositories\InMemoryInvoicesRepository;
    use bravik\Shared\Application\FlusherInterface;
    use bravik\Shared\Domain\Model\OId;
    use bravik\Shared\Tests\Mocks\EventDispatcherSpy;
    use PHPUnit\Framework\Attributes\CoversClass;
    use PHPUnit\Framework\TestCase;

    #[CoversClass(Handler::class)]
    final class PayTest extends TestCase
    {
        private Handler $handler;
        private InMemoryInvoicesRepository $invoicesRepository;
        private FlusherInterface $flusher;
        private EventDispatcherSpy $dispatcher;

        protected function setUp(): void
        {
            $this->invoicesRepository = new InMemoryInvoicesRepository();
            $this->flusher = $this->createMock(FlusherInterface::class);
            $this->dispatcher = new EventDispatcherSpy();

            $this->handler = new Handler(
                $this->invoicesRepository,
                $this->flusher,
                $this->dispatcher
            );
        }

        public function testSuccess(): void
        {
            // Use the preexisting invoice from InMemoryInvoicesRepository
            $invoice = $this->invoicesRepository->storage[0];
            $command = new \bravik\Sales\Application\UseCases\Commands\Invoices\Pay\Command(
                invoiceId: $invoice->getId(),
                transactionId: 'transaction-123'
            );

            $this->flusher
                ->expects($this->once())
                ->method('flush');

            $this->handler->handle($command);

            // Verify invoice state changes
            $updatedInvoice = $this->invoicesRepository->get($invoice->getId());
            self::assertTrue($updatedInvoice->isPaid());
            self::assertEquals('transaction-123', $updatedInvoice->getTransactionId());
            self::assertNotNull($updatedInvoice->getPaidAt());

            // Verify events
            $recordedEvents = $this->dispatcher->getRecordedEvents();
            self::assertCount(1, $recordedEvents);
            self::assertInstanceOf(InvoicePaidEvent::class, $recordedEvents[0]);
        }

        public function testFailsWhenInvoiceNotFound(): void
        {
            $command = new \bravik\Sales\Application\UseCases\Commands\Invoices\Pay\Command(
                invoiceId: OId::next(),
                transactionId: 'transaction-123'
            );

            $this->expectException(NotFoundException::class);
            $this->handler->handle($command);
        }

        public function testFailsWhenInvoiceAlreadyPaid(): void
        {
            // Get and pay the preexisting invoice first
            $invoice = $this->invoicesRepository->storage[0];
            $invoice->pay('first-transaction');

            $command = new \bravik\Sales\Application\UseCases\Commands\Invoices\Pay\Command(
                invoiceId: $invoice->getId(),
                transactionId: 'second-transaction'
            );

            $this->expectException(InvoiceIsNotAwaitingPaymentException::class);
            $this->handler->handle($command);
        }
    }