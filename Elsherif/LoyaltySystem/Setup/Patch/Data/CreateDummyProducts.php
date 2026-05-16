<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;

/**
 * Create dummy products and categories for testing
 */
class CreateDummyProducts implements DataPatchInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;
    private ProductFactory $productFactory;
    private CategoryFactory $categoryFactory;
    private ProductRepositoryInterface $productRepository;
    private CategoryRepositoryInterface $categoryRepository;
    private StoreManagerInterface $storeManager;
    private State $state;
    private ?SourceItemsSaveInterface $sourceItemsSave;
    private ?SourceItemInterfaceFactory $sourceItemFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager,
        State $state,
        ?SourceItemsSaveInterface $sourceItemsSave = null,
        ?SourceItemInterfaceFactory $sourceItemFactory = null
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->sourceItemsSave = $sourceItemsSave;
        $this->sourceItemFactory = $sourceItemFactory;
    }

    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        try {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch (\Exception $e) {
            // Area code already set
        }

        // Create Categories
        $categoryIds = $this->createCategories();

        // Create Products
        $this->createProducts($categoryIds);

        $this->moduleDataSetup->endSetup();
        return $this;
    }

    private function createCategories(): array
    {
        $categories = [
            ['name' => 'Electronics', 'url_key' => 'electronics'],
            ['name' => 'Clothing', 'url_key' => 'clothing'],
            ['name' => 'Home & Garden', 'url_key' => 'home-garden'],
            ['name' => 'Sports', 'url_key' => 'sports'],
        ];

        $categoryIds = [];
        $rootCategoryId = $this->storeManager->getStore()->getRootCategoryId();

        foreach ($categories as $catData) {
            try {
                $category = $this->categoryFactory->create();
                $category->setName($catData['name'])
                    ->setUrlKey($catData['url_key'])
                    ->setIsActive(true)
                    ->setParentId($rootCategoryId)
                    ->setStoreId(0)
                    ->setPath('1/' . $rootCategoryId);
                
                $savedCategory = $this->categoryRepository->save($category);
                $categoryIds[$catData['url_key']] = $savedCategory->getId();
            } catch (\Exception $e) {
                // Category might already exist
                continue;
            }
        }

        return $categoryIds;
    }

    private function createProducts(array $categoryIds): void
    {
        $products = [
            // Electronics
            ['sku' => 'LAPTOP-001', 'name' => 'Gaming Laptop Pro', 'price' => 1299.99, 'cat' => 'electronics'],
            ['sku' => 'PHONE-001', 'name' => 'Smartphone X12', 'price' => 899.99, 'cat' => 'electronics'],
            ['sku' => 'TABLET-001', 'name' => 'Digital Tablet 10"', 'price' => 499.99, 'cat' => 'electronics'],
            ['sku' => 'WATCH-001', 'name' => 'Smart Watch Series 5', 'price' => 349.99, 'cat' => 'electronics'],
            ['sku' => 'HEADPHONE-001', 'name' => 'Wireless Headphones', 'price' => 199.99, 'cat' => 'electronics'],
            
            // Clothing
            ['sku' => 'SHIRT-001', 'name' => 'Classic Cotton Shirt', 'price' => 49.99, 'cat' => 'clothing'],
            ['sku' => 'JEANS-001', 'name' => 'Slim Fit Jeans', 'price' => 79.99, 'cat' => 'clothing'],
            ['sku' => 'JACKET-001', 'name' => 'Winter Jacket', 'price' => 149.99, 'cat' => 'clothing'],
            ['sku' => 'SHOES-001', 'name' => 'Running Shoes', 'price' => 129.99, 'cat' => 'clothing'],
            ['sku' => 'HAT-001', 'name' => 'Baseball Cap', 'price' => 24.99, 'cat' => 'clothing'],
            
            // Home & Garden
            ['sku' => 'SOFA-001', 'name' => 'Modern Sofa Set', 'price' => 899.99, 'cat' => 'home-garden'],
            ['sku' => 'LAMP-001', 'name' => 'LED Desk Lamp', 'price' => 39.99, 'cat' => 'home-garden'],
            ['sku' => 'TABLE-001', 'name' => 'Dining Table', 'price' => 299.99, 'cat' => 'home-garden'],
            ['sku' => 'PLANT-001', 'name' => 'Indoor Plant Set', 'price' => 49.99, 'cat' => 'home-garden'],
            ['sku' => 'RUG-001', 'name' => 'Persian Style Rug', 'price' => 199.99, 'cat' => 'home-garden'],
            
            // Sports
            ['sku' => 'BIKE-001', 'name' => 'Mountain Bike', 'price' => 599.99, 'cat' => 'sports'],
            ['sku' => 'BALL-001', 'name' => 'Basketball', 'price' => 29.99, 'cat' => 'sports'],
            ['sku' => 'RACKET-001', 'name' => 'Tennis Racket', 'price' => 89.99, 'cat' => 'sports'],
            ['sku' => 'WEIGHTS-001', 'name' => 'Dumbbell Set', 'price' => 149.99, 'cat' => 'sports'],
            ['sku' => 'YOGA-001', 'name' => 'Yoga Mat Pro', 'price' => 39.99, 'cat' => 'sports'],
        ];

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $sourceItems = [];

        foreach ($products as $productData) {
            try {
                // Check if product exists
                try {
                    $this->productRepository->get($productData['sku']);
                    continue; // Skip if exists
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    // Product doesn't exist, create it
                }

                $product = $this->productFactory->create();
                $product->setSku($productData['sku'])
                    ->setName($productData['name'])
                    ->setTypeId(Type::TYPE_SIMPLE)
                    ->setAttributeSetId($product->getDefaultAttributeSetId())
                    ->setVisibility(Visibility::VISIBILITY_BOTH)
                    ->setStatus(Status::STATUS_ENABLED)
                    ->setPrice($productData['price'])
                    ->setWebsiteIds([$websiteId])
                    ->setStockData([
                        'is_in_stock' => 1,
                        'qty' => 100
                    ]);

                // Set category
                if (isset($categoryIds[$productData['cat']])) {
                    $product->setCategoryIds([$categoryIds[$productData['cat']]]);
                }

                $this->productRepository->save($product);

                // Add source item for MSI
                if ($this->sourceItemFactory && $this->sourceItemsSave) {
                    $sourceItem = $this->sourceItemFactory->create();
                    $sourceItem->setSourceCode('default');
                    $sourceItem->setSku($productData['sku']);
                    $sourceItem->setQuantity(100);
                    $sourceItem->setStatus(1);
                    $sourceItems[] = $sourceItem;
                }

            } catch (\Exception $e) {
                // Skip failed products
                continue;
            }
        }

        // Save source items
        if (!empty($sourceItems) && $this->sourceItemsSave) {
            try {
                $this->sourceItemsSave->execute($sourceItems);
            } catch (\Exception $e) {
                // Ignore MSI errors
            }
        }
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
