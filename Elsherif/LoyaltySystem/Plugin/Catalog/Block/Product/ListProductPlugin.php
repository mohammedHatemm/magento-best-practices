<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Plugin\Catalog\Block\Product;

use Magento\Catalog\Block\Product\ListProduct;
use Elsherif\LoyaltySystem\Model\Config;

/**
 * Plugin to add loyalty points data to product listing
 */
class ListProductPlugin
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Add loyalty points to product details HTML
     *
     * @param ListProduct $subject
     * @param string $result
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function afterGetProductDetailsHtml(
        ListProduct $subject,
        string $result,
        $product
    ): string {
        if (!$this->config->isEnabled()) {
            return $result;
        }

        $points = $this->getProductPoints($product);

        if ($points <= 0) {
            return $result;
        }

        $pointsHtml = sprintf(
            '<div class="loyalty-points-listing"><span class="icon">⭐</span> <span>+%d %s</span></div>',
            $points,
            __('Points')
        );

        return $pointsHtml . $result;
    }

    /**
     * Get points for product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int
     */
    private function getProductPoints($product): int
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

        return (int) floor($price / $earnRate);
    }
}
