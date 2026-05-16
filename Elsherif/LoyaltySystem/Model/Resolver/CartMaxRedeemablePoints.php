<?php
/**
 * GraphQL Resolver for Maximum Redeemable Points for Cart
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Helper\Config;
use Elsherif\LoyaltySystem\Model\PointsCalculator;

class CartMaxRedeemablePoints implements ResolverInterface
{
    private PointsManagementInterface $pointsManagement;
    private Config $config;
    private PointsCalculator $calculator;

    public function __construct(
        PointsManagementInterface $pointsManagement,
        Config $config,
        PointsCalculator $calculator
    ) {
        $this->pointsManagement = $pointsManagement;
        $this->config = $config;
        $this->calculator = $calculator;
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

        // Get customer's available points
        $balance = $this->pointsManagement->getBalance((int) $cart->getCustomerId());
        $availablePoints = $balance->getPoints();

        // Calculate max points based on cart total
        $cartTotal = (float) $cart->getGrandTotal();
        $redeemRate = $this->config->getRedeemRate();
        $maxByCart = (int) floor($cartTotal * $redeemRate);

        // Get config max per order
        $maxPerOrder = $this->config->getMaxPointsPerOrder();

        // Return minimum of all constraints
        return min($availablePoints, $maxByCart, $maxPerOrder ?: PHP_INT_MAX);
    }
}
