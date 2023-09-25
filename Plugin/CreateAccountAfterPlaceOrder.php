<?php

declare(strict_types=1);

namespace Growcode\CreateAccountAfterPlaceOrder\Plugin;

use Growcode\CreateAccountAfterPlaceOrder\Api\ConvertCartToCustomerInterface;
use Growcode\CreateAccountAfterPlaceOrder\Api\RemovePasswordHashFromQuoteInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteManagement as MagentoQuoteManagement;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class CreateAccount
 */
class CreateAccountAfterPlaceOrder
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly RemovePasswordHashFromQuoteInterface $removePasswordHashFromQuote,
        private readonly ConvertCartToCustomerInterface $convertCartToCustomer,
        private readonly AccountManagementInterface $accountManagement,
        private readonly Session $session,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly EncryptorInterface $encryptor
    ) {
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlaceOrder(
        MagentoQuoteManagement $subject,
        string $orderId,
        $cartId
    ): void {
        $quote = $this->cartRepository->get($cartId);

        if (!self::isSetCreateAccountData($quote)) {
            return;
        }
        $customer = $this->createAccountFromQuote($quote);
        $this->initiateSession($customer);
        $this->clearAccountDataFromQuote($quote);
        $order = $this->orderRepository->get($orderId);
        $order->setCustomerId($customer->getId());
        $this->orderRepository->save($order);
    }

    private static function isSetCreateAccountData(CartInterface $quote): bool
    {
        return (bool) $quote->getCustomerPasswordCiphertext();
    }

    /**
     * @throws LocalizedException
     */
    private function createAccountFromQuote(CartInterface $quote): CustomerInterface
    {
        return $this->accountManagement->createAccount(
            $this->convertCartToCustomer->execute($quote),
            $this->encryptor->decrypt($quote->getCustomerPasswordCiphertext())
        );
    }

    private function initiateSession(CustomerInterface $customer): void
    {
        $this->session->setCustomerDataAsLoggedIn($customer);
    }

    private function clearAccountDataFromQuote(CartInterface $quote): void
    {
        $this->removePasswordHashFromQuote->execute((int) $quote->getId());
    }
}
