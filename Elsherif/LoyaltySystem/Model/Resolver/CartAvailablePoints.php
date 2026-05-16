<?php
/**
 * GraphQL Resolver for Customer's Available Points in Cart Context
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Helper\Config;

class CartAvailablePoints implements ResolverInterface
{
    private PointsManagementInterface $pointsManagement;
    private Config $config;

    public function __construct(
        PointsManagementInterface $pointsManagement,
        Config $config
    ) {
        $this->pointsManagement = $pointsManagement;
        $this->config = $config;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): ?int {
        if (!$this->config->isEnabled()) {
            return null;
        }

        /** @var Quote $cart */
        $cart = $value['model'] ?? null;
        
        if (!$cart || !$cart->getCustomerId()) {
            return 0;
        }

        $balance = $this->pointsManagement->getBalance((int) $cart->getCustomerId());
        
        return $balance->getPoints();
    }
}
