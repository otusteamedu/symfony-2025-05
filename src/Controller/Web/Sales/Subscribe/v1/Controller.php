<?php

namespace App\Controller\Web\Sales\Subscribe\v1;

use App\Controller\Web\Sales\Subscribe\v1\Input\SubscribeDTO;
use bravik\Sales\Presentation\Contract\SalesInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final readonly class Controller
{
    public function __construct(
        private SalesInterface $sales,
    ) {
    }

    #[Route(path: 'api/v1/create-paid-subscription', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] SubscribeDTO $subscribeDTO,
    ): JsonResponse {
        try {
            $subscriptionId = $this->sales->subscribe(
                $subscribeDTO->userId,
                $subscribeDTO->productId,
                new \DateTimeImmutable()
            );
        } catch (\Exception $e) {
            // TODO Handle
            throw new $e;
        }

        return new JsonResponse([
            'subscriptionId' => $subscriptionId
        ]);
    }
}
