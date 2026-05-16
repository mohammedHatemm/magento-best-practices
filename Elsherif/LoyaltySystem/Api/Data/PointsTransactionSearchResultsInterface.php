<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PointsTransactionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get points transaction list
     *
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface[]
     */
    public function getItems();

    /**
     * Set points transaction list
     *
     * @param \Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
