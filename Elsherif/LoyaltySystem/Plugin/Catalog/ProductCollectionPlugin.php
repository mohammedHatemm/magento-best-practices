<?php
/**
 * Plugin to add loyalty_points attribute to product collection
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Plugin\Catalog;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Elsherif\LoyaltySystem\Model\Config;

class ProductCollectionPlugin
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Add loyalty_points attribute to collection
     */
    public function beforeLoad(Collection $subject, $printQuery = false, $logQuery = false): array
    {
        if ($this->config->isEnabled() && !$subject->isLoaded()) {
            $subject->addAttributeToSelect('loyalty_points');
        }
        
        return [$printQuery, $logQuery];
    }
}
