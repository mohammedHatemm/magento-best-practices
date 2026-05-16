<?php
/**
 * GraphQL Resolver for Product Loyalty Points
 * Returns points earned when purchasing a product
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Elsherif\LoyaltySystem\Helper\Config;

class ProductLoyaltyPoints implements ResolverInterface
{
    private Config $config;
    private ConfigurableResource $configurableResource;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        Config $config,
        ConfigurableResource $configurableResource,
        ProductRepositoryInterface $productRepository
    ) {
        $this->config = $config;
        $this->configurableResource = $configurableResource;
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

        $product = $value['model'] ?? null;
        
        if (!$product) {
            return null;
        }

        // Get loyalty_points attribute value
        $loyaltyPoints = (int) $product->getData('loyalty_points');
        
        // If no points on this product, check if it's a child of configurable
        if ($loyaltyPoints <= 0) {
            $loyaltyPoints = $this->getParentProductPoints($product);
        }

        // If still no points, calculate based on price
        if ($loyaltyPoints <= 0) {
            $price = (float) $product->getFinalPrice();
            $earnRate = $this->config->getEarnRate();
            $loyaltyPoints = $earnRate > 0 ? (int) floor($price / $earnRate) : 0;
        }
        
        return $loyaltyPoints;
    }

    /**
     * Get loyalty points from parent configurable product
     */
    private function getParentProductPoints($product): int
    {
        try {
            $parentIds = $this->configurableResource->getParentIdsByChild($product->getId());
            
            if (!empty($parentIds)) {
                $parentId = reset($parentIds);
                $parentProduct = $this->productRepository->getById($parentId);
                return (int) $parentProduct->getData('loyalty_points');
            }
        } catch (\Exception $e) {
            // Parent product not found
        }
        
        return 0;
    }
}
