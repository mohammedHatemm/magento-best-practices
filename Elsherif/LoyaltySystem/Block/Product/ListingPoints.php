<?php
/**
 * Block to provide loyalty points data for product listing
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Block\Product;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Elsherif\LoyaltySystem\Model\Config;

class ListingPoints extends Template
{
    private Config $config;
    private LayerResolver $layerResolver;
    private ProductRepositoryInterface $productRepository;
    private Json $json;

    public function __construct(
        Context $context,
        Config $config,
        LayerResolver $layerResolver,
        ProductRepositoryInterface $productRepository,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->layerResolver = $layerResolver;
        $this->productRepository = $productRepository;
        $this->json = $json;
    }

    /**
     * Check if loyalty system is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * Get product points data as JSON
     */
    public function getProductPointsJson(): string
    {
        $products = [];
        
        try {
            $layer = $this->layerResolver->get();
            $collection = $layer->getProductCollection();
            
            // Make sure loyalty_points attribute is loaded
            $collection->addAttributeToSelect('loyalty_points');
            
            foreach ($collection as $product) {
                $productId = $product->getId();
                $points = $this->getProductPoints($product);
                
                if ($points > 0) {
                    $products[$productId] = [
                        'points' => $points,
                        'sku' => $product->getSku(),
                        'url_key' => $product->getUrlKey(),
                        'type' => $product->getTypeId()
                    ];
                }
            }
        } catch (\Exception $e) {
            // Return empty if layer not available
        }
        
        return $this->json->serialize($products);
    }

    /**
     * Get points for a product (handles all product types)
     */
    private function getProductPoints($product): int
    {
        // Try to get from attribute first
        $points = (int) $product->getData('loyalty_points');
        
        // If not loaded, try to reload
        if ($points <= 0) {
            try {
                $loadedProduct = $this->productRepository->getById($product->getId());
                $points = (int) $loadedProduct->getData('loyalty_points');
            } catch (\Exception $e) {
                // Product not found
            }
        }
        
        // Calculate default if still no points
        if ($points <= 0) {
            $earnRate = $this->config->getEarnRate();
            $price = (float) $product->getFinalPrice();
            
            // For configurable, get min price
            if ($product->getTypeId() === 'configurable') {
                $price = (float) $product->getPriceInfo()
                    ->getPrice('final_price')
                    ->getMinimalPrice()
                    ->getValue();
            }
            
            $points = $earnRate > 0 ? (int) floor($price / $earnRate) : 0;
        }
        
        return $points;
    }
}
