<?php

namespace App\Controller\Web\Sales\GeneratePaymentLink\v1;

use bravik\Sales\Presentation\Contract\SalesInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class Controller
{
    public function __construct(
        private readonly SalesInterface $sales)
    {
    }

    #[Route(path: 'api/v1/generate-payment-link/{subscriptionId}', methods: ['GET'])]
    public function __invoke(string $subscriptionId): JsonResponse
    {
        try {
            return new JsonResponse([
                'paymentLink' => $this->sales->generatePaymentLink($subscriptionId),
            ]);
        } catch (\Exception $e) {
            // TODO Handle exceptions
            throw $e;
        }
    }
}
