<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PointsBalanceSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get points balance list
     *
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface[]
     */
    public function getItems();

    /**
     * Set points balance list
     *
     * @param \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
