<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Plugin\Order;

use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Model\Config;
use Elsherif\LoyaltySystem\Helper\Data as DataHelper;
use Psr\Log\LoggerInterface;

/**
 * Plugin to earn loyalty points when order is placed
 */
class EarnPointsPlugin
{
    private PointsManagementInterface $pointsManagement;
    private Config $config;
    private DataHelper $dataHelper;
    private LoggerInterface $logger;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        PointsManagementInterface $pointsManagement,
        Config $config,
        DataHelper $dataHelper,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository
    ) {
        $this->pointsManagement = $pointsManagement;
        $this->config = $config;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
    }

    /**
     * Earn points after order is placed
     */
    public function afterPlace(
        OrderManagementInterface $subject,
        OrderInterface $result,
        OrderInterface $order
    ): OrderInterface {
        try {
            if (!$this->config->isEnabled()) {
                return $result;
            }

            $customerId = (int) $result->getCustomerId();
            if (!$customerId) {
                return $result;
            }

            $totalPoints = $this->calculateOrderPoints($result);

            if ($totalPoints <= 0) {
                return $result;
            }

            $expiresAt = $this->dataHelper->getExpirationDate();

            $this->pointsManagement->addPoints(
                $customerId,
                $totalPoints,
                'order_complete',
                (int) $result->getEntityId(),
                $expiresAt,
                "Earned from order #{$result->getIncrementId()}"
            );

            $result->setData('loyalty_points_earned', $totalPoints);

            $this->logger->info(
                "Loyalty: Added {$totalPoints} points to customer {$customerId} for order #{$result->getIncrementId()}"
            );

        } catch (\Exception $e) {
            $this->logger->error('Loyalty Plugin Error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Calculate total points from order items
     */
    private function calculateOrderPoints(OrderInterface $order): int
    {
        $totalPoints = 0;

        foreach ($order->getAllVisibleItems() as $item) {
            $productPoints = $this->getProductLoyaltyPoints($item);

            // If no product points set, use default calculation
            if ($productPoints <= 0) {
                $productPoints = $this->calculateDefaultPoints($item);
            }

            // Multiply by quantity
            $itemPoints = $productPoints * (int) $item->getQtyOrdered();
            $totalPoints += $itemPoints;

            $this->logger->debug(
                "Loyalty: Item {$item->getSku()} - Points: {$productPoints} x Qty: {$item->getQtyOrdered()} = {$itemPoints}"
            );
        }

        return $totalPoints;
    }

    /**
     * Get loyalty points for a product, handling configurable products
     */
    private function getProductLoyaltyPoints($item): int
    {
        $productPoints = 0;
        
        try {
            // First try to get points from the ordered product
            $productId = (int) $item->getProductId();
            
            if ($productId) {
                $product = $this->productRepository->getById($productId);
                $productPoints = (int) $product->getData('loyalty_points');
                
                // If no points on child product, check parent (for configurable products)
                if ($productPoints <= 0) {
                    $parentProductId = $this->getParentProductId($item);
                    if ($parentProductId) {
                        $parentProduct = $this->productRepository->getById($parentProductId);
                        $productPoints = (int) $parentProduct->getData('loyalty_points');
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning('Loyalty: Could not get product points: ' . $e->getMessage());
        }
        
        return $productPoints;
    }

    /**
     * Get parent product ID for configurable product children
     */
    private function getParentProductId($item): ?int
    {
        // Check if item has parent item (configurable product)
        $parentItem = $item->getParentItem();
        if ($parentItem) {
            return (int) $parentItem->getProductId();
        }

        // Alternative: Check product options for parent
        $productOptions = $item->getProductOptions();
        if (isset($productOptions['info_buyRequest']['product'])) {
            $parentId = (int) $productOptions['info_buyRequest']['product'];
            if ($parentId !== (int) $item->getProductId()) {
                return $parentId;
            }
        }

        return null;
    }

    /**
     * Calculate default points based on price
     */
    private function calculateDefaultPoints($item): int
    {
        $earnRate = $this->config->getEarnRate();
        $rowTotal = (float) $item->getRowTotal();
        
        // Subtract discount if applicable
        $rowTotal -= (float) $item->getDiscountAmount();
        
        if ($rowTotal <= 0 || $earnRate <= 0) {
            return 0;
        }

        // Calculate: price / earnRate = points
        // If earnRate = 1, then $100 = 100 points (1 point per $1)
        return (int) floor($rowTotal / $earnRate);
    }
}
