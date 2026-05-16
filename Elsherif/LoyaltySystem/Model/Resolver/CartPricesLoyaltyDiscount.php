<?php
/**
 * GraphQL Resolver for CartPrices Loyalty Discount
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;

class CartPricesLoyaltyDiscount implements ResolverInterface
{
    private StoreManagerInterface $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): ?array {
        /** @var Quote $cart */
        $cart = $value['model'] ?? null;
        
        if (!$cart) {
            return null;
        }

        $discountAmount = (float) $cart->getData('loyalty_discount_amount');
        
        if ($discountAmount <= 0) {
            return null;
        }

        $currency = $this->storeManager->getStore()->getCurrentCurrencyCode();

        return [
            'value' => -$discountAmount, // Negative for discount
            'currency' => $currency
        ];
    }
}
