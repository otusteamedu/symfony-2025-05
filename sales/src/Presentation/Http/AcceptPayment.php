<?php

declare(strict_types=1);

namespace bravik\Sales\Presentation\Http;

use bravik\Sales\Application\UseCases\Commands\Invoices\Pay\Command;
use bravik\Sales\Application\UseCases\Commands\Invoices\Pay\Handler;
use bravik\Sales\Domain\Exceptions\InvoiceIsNotAwaitingPaymentException;
use bravik\Sales\Domain\Exceptions\NotFoundException;
use bravik\Shared\Domain\Model\OId;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Webmozart\Assert\Assert;

/**
 * Вебхук для платежной системы реализую прямо в модуле Sales, вне основного API монолита.
 */
#[AsController]
class AcceptPayment
{
    public function __construct(
        private Handler $payInvoiceUseCase
    ) {
    }

    #[Route('/api/sales/webhooks/invoices/pay', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $requestData = $request->toArray();
        $invoiceId = $requestData['invoice_id'] ?? null;
        $transactionId = $requestData['transaction_id'] ?? null;

        // TODO Validate with validator
        try {
            Assert::stringNotEmpty($invoiceId);
            Assert::stringNotEmpty($transactionId);
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        try {
            $this->payInvoiceUseCase->handle(
                new Command(
                    invoiceId: OId::fromString($invoiceId),
                    transactionId: $transactionId,
                )
            );

            return new JsonResponse();
        } catch (NotFoundException|InvoiceIsNotAwaitingPaymentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}