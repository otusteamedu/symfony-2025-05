<?php

namespace App\Controller\Web\Sales\Subscribe\v1\Input;

class SubscribeDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $productId
    ) {
    }
}
