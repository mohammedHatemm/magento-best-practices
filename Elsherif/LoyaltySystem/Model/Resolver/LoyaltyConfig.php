<?php
/**
 * GraphQL Resolver for Loyalty System Configuration
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Elsherif\LoyaltySystem\Helper\Config;

class LoyaltyConfig implements ResolverInterface
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        return [
            'is_enabled' => $this->config->isEnabled(),
            'earn_rate' => $this->config->getEarnRate(),
            'redeem_rate' => $this->config->getRedeemRate(),
            'min_points_to_redeem' => $this->config->getMinPointsToRedeem(),
            'max_points_per_order' => $this->config->getMaxPointsPerOrder(),
            'points_expiry_days' => $this->config->getPointsExpiryDays(),
            'allow_partial_redemption' => true // Can be moved to config
        ];
    }
}
