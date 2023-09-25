<?php

declare(strict_types=1);

namespace Growcode\CreateAccountAfterPlaceOrder\Model;

use Growcode\CreateAccountAfterPlaceOrder\Api\CheckPasswordStrengthInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\StringUtils;

class CheckPasswordStrength implements CheckPasswordStrengthInterface
{
    public function __construct(
        private readonly StringUtils $stringUtils,
        private readonly ScopeConfigInterface $scopeConfig,
    ) {
    }

    public function execute(string $password): void
    {
        $length = $this->stringUtils->strlen($password);
        if ($length > AccountManagementInterface::MAX_PASSWORD_LENGTH) {
            throw new InputException(
                __(
                    'Please enter a password with at most %1 characters.',
                    AccountManagementInterface::MAX_PASSWORD_LENGTH
                )
            );
        }
        $configMinPasswordLength = $this->getMinPasswordLength();
        if ($length < $configMinPasswordLength) {
            throw new InputException(
                __(
                    'The password needs at least %1 characters. Create a new password and try again.',
                    $configMinPasswordLength
                )
            );
        }
        $trimmedPassLength = $this->stringUtils->strlen(trim($password));
        if ($trimmedPassLength != $length) {
            throw new InputException(
                __("The password can't begin or end with a space. Verify the password and try again.")
            );
        }

        $requiredCharactersCheck = $this->makeRequiredCharactersCheck($password);
        if ($requiredCharactersCheck !== 0) {
            throw new InputException(
                __(
                    'Minimum of different classes of characters in password is %1.' .
                    ' Classes of characters: Lower Case, Upper Case, Digits, Special Characters.',
                    $requiredCharactersCheck
                )
            );
        }
    }

    private function getMinPasswordLength(): int
    {
        return (int) $this->scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    private function makeRequiredCharactersCheck(string $password): int
    {
        $counter = 0;
        $return = 0;
        $requiredNumber = (int) $this->scopeConfig->getValue(
            AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER
        );

        if (preg_match('/[0-9]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[A-Z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[a-z]+/', $password)) {
            $counter++;
        }
        if (preg_match('/[^a-zA-Z0-9]+/', $password)) {
            $counter++;
        }

        if ($counter < $requiredNumber) {
            $return = $requiredNumber;
        }

        return $return;
    }
}
