<?php
/**
 * GraphQL Resolver for Potential Points Earned from Cart
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Elsherif\LoyaltySystem\Helper\Config;

class CartPotentialPoints implements ResolverInterface
{
    private Config $config;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        Config $config,
        ProductRepositoryInterface $productRepository
    ) {
        $this->config = $config;
        $this->productRepository = $productRepository;
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
        
        if (!$cart) {
            return 0;
        }

        $totalPoints = 0;

        foreach ($cart->getAllVisibleItems() as $item) {
            $productPoints = $this->getProductLoyaltyPoints($item);
            
            // If no product points, calculate based on price
            if ($productPoints <= 0) {
                $rowTotal = (float) $item->getRowTotal();
                $earnRate = $this->config->getEarnRate();
                $productPoints = $earnRate > 0 ? (int) floor($rowTotal / $earnRate) : 0;
            } else {
                // Multiply by quantity
                $productPoints = $productPoints * (int) $item->getQty();
            }

            $totalPoints += $productPoints;
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
            $product = $item->getProduct();
            
            if ($product) {
                $productPoints = (int) $product->getData('loyalty_points');
                
                // If no points on simple product (child of configurable), check parent
                if ($productPoints <= 0 && $item->getParentItem()) {
                    $parentProduct = $item->getParentItem()->getProduct();
                    if ($parentProduct) {
                        $productPoints = (int) $parentProduct->getData('loyalty_points');
                    }
                }
                
                // If still no points, try loading from repository
                if ($productPoints <= 0) {
                    $loadedProduct = $this->productRepository->getById($product->getId());
                    $productPoints = (int) $loadedProduct->getData('loyalty_points');
                }
            }
        } catch (\Exception $e) {
            // Silently fail and use default calculation
        }
        
        return $productPoints;
    }
}
