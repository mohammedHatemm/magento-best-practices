<?php
/**
 * GraphQL Resolver for Cart Loyalty Discount Amount
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\StoreManagerInterface;

class CartLoyaltyDiscount implements ResolverInterface
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

        $extensionAttributes = $cart->getExtensionAttributes();
        $discountAmount = 0.0;
        
        if ($extensionAttributes && method_exists($extensionAttributes, 'getLoyaltyDiscountAmount')) {
            $discountAmount = (float) $extensionAttributes->getLoyaltyDiscountAmount();
        } else {
            $discountAmount = (float) $cart->getData('loyalty_discount_amount');
        }

        if ($discountAmount <= 0) {
            return null;
        }

        $currency = $this->storeManager->getStore()->getCurrentCurrencyCode();

        return [
            'value' => $discountAmount,
            'currency' => $currency
        ];
    }
}
