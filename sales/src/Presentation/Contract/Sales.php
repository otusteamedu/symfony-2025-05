<?php

declare(strict_types=1);

namespace bravik\Sales\Presentation\Contract;

use bravik\Sales\Application\UseCases\Commands\Subscriptions\Create\Command as CreateSubscriptionCommand;
use bravik\Sales\Application\UseCases\Commands\Subscriptions\Create\Handler as CreateSubscriptionHandler;
use bravik\Sales\Application\UseCases\Queries\Invoices\GeneratePaymentLink\Fetcher as GeneratePaymentLinkFetcher;
use bravik\Sales\Application\UseCases\Queries\Invoices\GeneratePaymentLink\Query as GeneratePaymentLinkQuery;
use bravik\Shared\Domain\Model\OId;
use DateTimeImmutable;
use Exception;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Throwable;

/**
 * Реализация контракта с прямым доступом к коду.
 *
 * Использует механизм Symfony Service Subscriber, чтобы не инстанциировать все сервисы одновременно.
 * https://symfony.com/doc/current/service_container/service_subscribers_locators.html
 */
final readonly class Sales implements SalesInterface, ServiceSubscriberInterface
{
    public function __construct(
        private ContainerInterface $locator
    ) {
    }

    public function subscribe(
        string $customerId,
        string $productId,
        DateTimeImmutable $startDate,
    ): string {
        $command = new CreateSubscriptionCommand(
            customerId: OId::fromString($customerId),
            productId: OId::fromString($productId),
            startDate: $startDate
        );

        $handler = $this->service(CreateSubscriptionHandler::class);

        try {
            $subscriptionId = $handler->handle($command);
        } catch (Throwable $e) {
            // TODO Convert exception to contract
            throw $e;
        }

        return $subscriptionId;
    }

    public function generatePaymentLink(string $subscriptionId): string
    {
        $fetcher = $this->service(GeneratePaymentLinkFetcher::class);

        try {
            $result = $fetcher->fetch(new GeneratePaymentLinkQuery(
                subscriptionId: $subscriptionId
            ));

            return $result->paymentLink;
        } catch (Exception $e) {
            // TODO Convert exception to contract
            throw $e;
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            CreateSubscriptionHandler::class => CreateSubscriptionHandler::class,
            GeneratePaymentLinkFetcher::class => GeneratePaymentLinkFetcher::class,
        ];
    }

    /**
     * @template T of object
     * @param class-string<T> $service
     * @return T
     */
    private function service(string $service)
    {
        return $this->locator->get($service);
    }
}