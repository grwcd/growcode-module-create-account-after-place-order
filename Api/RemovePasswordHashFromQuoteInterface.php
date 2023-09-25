<?php

declare(strict_types=1);

namespace Growcode\CreateAccountAfterPlaceOrder\Api;

interface RemovePasswordHashFromQuoteInterface
{
    public function execute(int $cartId): void;
}
