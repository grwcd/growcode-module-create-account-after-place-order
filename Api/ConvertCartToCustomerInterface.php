<?php

declare(strict_types=1);

namespace Growcode\CreateAccountAfterPlaceOrder\Api;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Quote\Api\Data\CartInterface;

interface ConvertCartToCustomerInterface
{
    public function execute(CartInterface $cart): CustomerInterface;
}
