<?php
/**
 * GraphQL Resolver for Cart Loyalty Points Used
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;

class CartLoyaltyPoints implements ResolverInterface
{
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): ?int {
        /** @var Quote $cart */
        $cart = $value['model'] ?? null;
        
        if (!$cart) {
            return null;
        }

        $extensionAttributes = $cart->getExtensionAttributes();
        
        if ($extensionAttributes && method_exists($extensionAttributes, 'getLoyaltyPointsUsed')) {
            return (int) $extensionAttributes->getLoyaltyPointsUsed();
        }

        // Fallback to direct column
        return (int) $cart->getData('loyalty_points_used');
    }
}
