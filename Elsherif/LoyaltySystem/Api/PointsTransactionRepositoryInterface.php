<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Api;

use Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface PointsTransactionRepositoryInterface
{
    /**
     * Save transaction
     *
     * @param \Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface $transaction
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface
     */
    public function save(PointsTransactionInterface $transaction): PointsTransactionInterface;

    /**
     * Get by ID
     *
     * @param int $transactionId
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface
     */
    public function getById(int $transactionId): PointsTransactionInterface;

    /**
     * Get list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsTransactionSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete
     *
     * @param \Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface $transaction
     * @return bool
     */
    public function delete(PointsTransactionInterface $transaction): bool;
}
