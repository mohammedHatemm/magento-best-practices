<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Elsherif\LoyaltySystem\Model\Config;

/**
 * Block to display loyalty points on product page
 */
class Points extends Template
{
    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Config $config
     * @param ProductRepositoryInterface $productRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Config $config,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->config = $config;
        $this->productRepository = $productRepository;
    }

    /**
     * Check if loyalty system is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * Get current product
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get loyalty points for current product
     *
     * @return int
     */
    public function getProductPoints(): int
    {
        $product = $this->getProduct();
        
        if (!$product) {
            return 0;
        }

        // Get points from product attribute
        $points = (int) $product->getData('loyalty_points');

        // If no points set, calculate default
        if ($points <= 0) {
            $points = $this->calculateDefaultPoints($product);
        }

        return $points;
    }

    /**
     * Get points for a specific product (for listing)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    public function getPointsForProduct($product): int
    {
        // Get points from product attribute
        $points = (int) $product->getData('loyalty_points');

        // If no points set, calculate default
        if ($points <= 0) {
            $points = $this->calculateDefaultPoints($product);
        }

        return $points;
    }

    /**
     * Calculate default points based on price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    private function calculateDefaultPoints($product): int
    {
        $earnRate = $this->config->getEarnRate();
        $price = (float) $product->getFinalPrice();

        if ($price <= 0 || $earnRate <= 0) {
            return 0;
        }

        // Calculate: price / earnRate = points
        return (int) floor($price / $earnRate);
    }

    /**
     * Get formatted points message
     *
     * @return string
     */
    public function getPointsMessage(): string
    {
        $points = $this->getProductPoints();
        
        if ($points <= 0) {
            return '';
        }

        return (string) __('Earn %1 loyalty points with this purchase!', $points);
    }

    /**
     * Check if should show points
     *
     * @return bool
     */
    public function shouldShow(): bool
    {
        return $this->isEnabled() && $this->getProductPoints() > 0;
    }

    /**
     * Get points worth in currency
     *
     * @return float
     */
    public function getPointsWorth(): float
    {
        $points = $this->getProductPoints();
        $redeemRate = $this->config->getRedeemRate();

        if ($redeemRate <= 0) {
            return 0.0;
        }

        return $points / $redeemRate;
    }
}
