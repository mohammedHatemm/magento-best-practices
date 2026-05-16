<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Elsherif\LoyaltySystem\Model\Config;

/**
 * ViewModel for product loyalty points
 */
class ProductPoints implements ArgumentInterface
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
     * Check if loyalty system is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * Get points for a product
     *
     * @param ProductInterface $product
     * @return int
     */
    public function getPoints(ProductInterface $product): int
    {
        if (!$this->isEnabled()) {
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
     * Get formatted points HTML
     *
     * @param ProductInterface $product
     * @return string
     */
    public function getPointsHtml(ProductInterface $product): string
    {
        $points = $this->getPoints($product);

        if ($points <= 0) {
            return '';
        }

        return sprintf(
            '<div class="loyalty-points-listing"><span class="icon">⭐</span> <span>+%d %s</span></div>',
            $points,
            __('Points')
        );
    }

    /**
     * Calculate default points based on price
     *
     * @param ProductInterface $product
     * @return int
     */
    private function calculateDefaultPoints(ProductInterface $product): int
    {
        $earnRate = $this->config->getEarnRate();
        $price = (float) $product->getFinalPrice();

        if ($price <= 0 || $earnRate <= 0) {
            return 0;
        }

        return (int) floor($price / $earnRate);
    }
}
