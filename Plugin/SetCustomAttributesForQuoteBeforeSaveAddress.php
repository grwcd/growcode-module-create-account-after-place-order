<?php

declare(strict_types=1);

namespace Growcode\CreateAccountAfterPlaceOrder\Plugin;

use Growcode\CreateAccountAfterPlaceOrder\Api\CheckPasswordStrengthInterface;
use Magento\Checkout\Api\Data\ShippingInformationExtensionInterface;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;

class SetCustomAttributesForQuoteBeforeSaveAddress
{
    public function __construct(
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly EncryptorInterface $encryptor,
        private readonly CheckPasswordStrengthInterface $checkPasswordStrength
    ) {
    }

    /**
     * @throws NoSuchEntityException
     * @throws InputException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $shippingInformation
    ): void {
        $extensionAttributes = $shippingInformation->getExtensionAttributes();
        if (!self::isSetCreateAccountData($extensionAttributes)) {
            return;
        }
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setCustomerPasswordCiphertext(
            $this->getCustomerPasswordCiphertext(
                (string) $extensionAttributes->getAccountRegistrationPassword()
            )
        );
    }

    private static function isSetCreateAccountData(?ShippingInformationExtensionInterface $extensionAttributes): bool
    {
        return $extensionAttributes && $extensionAttributes->getAccountRegistrationPassword();
    }

    /**
     * @throws InputException
     */
    private function getCustomerPasswordCiphertext(string $password): string
    {
        $this->checkPasswordStrength->execute($password);
        return $this->encryptor->encrypt($password);
    }
}
