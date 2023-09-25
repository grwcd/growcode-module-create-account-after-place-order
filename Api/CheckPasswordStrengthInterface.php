<?php

declare(strict_types=1);

namespace Growcode\CreateAccountAfterPlaceOrder\Api;

use Magento\Framework\Exception\InputException;

/**
 * @see \Magento\Customer\Model\AccountManagement::checkPasswordStrength()
 */
interface CheckPasswordStrengthInterface
{
    /**
     * @throws InputException
     */
    public function execute(string $password): void;
}
