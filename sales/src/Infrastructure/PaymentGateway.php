<?php

declare(strict_types=1);

namespace bravik\Sales\Infrastructure;

use bravik\Sales\Application\PaymentGatewayInterface;

class PaymentGateway implements PaymentGatewayInterface
{
    public function getPaymentLink(string $invoiceId): string
    {
        return "https://payment-gateway.com/pay?invoiceId=$invoiceId";
    }
}