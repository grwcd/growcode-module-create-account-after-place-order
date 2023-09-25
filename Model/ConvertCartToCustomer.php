<?php

declare(strict_types=1);

namespace Growcode\CreateAccountAfterPlaceOrder\Model;

use Growcode\CreateAccountAfterPlaceOrder\Api\ConvertCartToCustomerInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Quote\Api\Data\AddressInterface as QuoteAddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address;

class ConvertCartToCustomer implements ConvertCartToCustomerInterface
{
    private const FIELD_MAP = [
        CustomerInterface::EMAIL => OrderInterface::CUSTOMER_EMAIL,
        CustomerInterface::FIRSTNAME => OrderInterface::CUSTOMER_FIRSTNAME,
        CustomerInterface::LASTNAME => OrderInterface::CUSTOMER_LASTNAME,
        CustomerInterface::MIDDLENAME => OrderInterface::CUSTOMER_MIDDLENAME,
        CustomerInterface::STORE_ID => OrderInterface::STORE_ID,
        CustomerInterface::PREFIX => OrderInterface::CUSTOMER_PREFIX,
        CustomerInterface::SUFFIX => OrderInterface::CUSTOMER_SUFFIX,
    ];

    public function __construct(
        private readonly CustomerInterfaceFactory $customerFactory,
        private readonly AddressInterfaceFactory $addressFactory,
        private readonly DataObjectHelper $dataObjectHelper
    ) {
    }

    public function execute(CartInterface $cart): CustomerInterface
    {
        $customer = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $this->collectDataFromCart($cart),
            CustomerInterface::class
        );
        $customer->setAddresses(
            $this->collectAddressesFromCart($cart)
        );
        return $customer;
    }

    private function collectDataFromCart(CartInterface $cart): array
    {
        $result = [];
        $cartData = $cart->toArray();
        foreach (self::FIELD_MAP as $customerField => $cartField) {
            $result[$customerField] = $cartData[$cartField] ?? null;
        }
        return $result;
    }

    private function collectAddressesFromCart(CartInterface $cart): array
    {
        $result = [];
        /** @var QuoteAddressInterface $address */
        foreach ($cart->getAddressesCollection() as $quoteAddress) {
            $customerAddress = $this->convertAddress($quoteAddress);
            if ($quoteAddress[QuoteAddressInterface::SAME_AS_BILLING]) {
                continue;
            }
            $result[] = $customerAddress;
        }
        return $result;
    }

    private function convertAddress(QuoteAddressInterface $address): CustomerAddressInterface
    {
        $result = $this->addressFactory->create();
        $addressData = $address->toArray();
        $addressData[CustomerAddressInterface::STREET] = explode('\n', $addressData[CustomerAddressInterface::STREET]);
        unset($addressData[CustomerAddressInterface::REGION]);
        if ($addressData[OrderAddressInterface::ADDRESS_TYPE] === Address::TYPE_SHIPPING) {
            $addressData[CustomerAddressInterface::DEFAULT_SHIPPING] = 1;
        } elseif ($addressData[OrderAddressInterface::ADDRESS_TYPE] === Address::TYPE_BILLING) {
            $addressData[CustomerAddressInterface::DEFAULT_BILLING] = 1;
        }
        $this->dataObjectHelper->populateWithArray(
            $result,
            $addressData,
            CustomerAddressInterface::class
        );
        return $result;
    }
}
