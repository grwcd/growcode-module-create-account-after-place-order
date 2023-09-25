<?php

declare(strict_types=1);

namespace Growcode\CreateAccountAfterPlaceOrder\Model\ResourceModel;

use Growcode\CreateAccountAfterPlaceOrder\Api\RemovePasswordHashFromQuoteInterface;
use Magento\Framework\App\ResourceConnection;

class RemovePasswordHashFromQuote implements RemovePasswordHashFromQuoteInterface
{
    public function __construct(
        private readonly ResourceConnection $resourceConnection
    ) {
    }

    public function execute(int $cartId): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $this->resourceConnection->getTableName('quote'),
            ['customer_password_ciphertext' => null],
            $connection->quoteInto('entity_id = ?', $cartId)
        );
    }
}
