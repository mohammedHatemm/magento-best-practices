<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Api;

use Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface PointsBalanceRepositoryInterface
{
    /**
     * Save balance
     *
     * @param \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface $balance
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface
     */
    public function save(PointsBalanceInterface $balance): PointsBalanceInterface;

    /**
     * Get by ID
     *
     * @param int $balanceId
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface
     */
    public function getById(int $balanceId): PointsBalanceInterface;

    /**
     * Get by customer ID
     *
     * @param int $customerId
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface
     */
    public function getByCustomerId(int $customerId): PointsBalanceInterface;

    /**
     * Get list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsBalanceSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete
     *
     * @param \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface $balance
     * @return bool
     */
    public function delete(PointsBalanceInterface $balance): bool;
}
