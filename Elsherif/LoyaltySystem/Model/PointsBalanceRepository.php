<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model;

use Elsherif\LoyaltySystem\Api\PointsBalanceRepositoryInterface;
use Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface;
use Elsherif\LoyaltySystem\Model\PointsBalanceFactory;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsBalance as BalanceResource;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsBalance\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class PointsBalanceRepository implements PointsBalanceRepositoryInterface
{
    private $balanceFactory;
    private $balanceResource;
    private $collectionFactory;

    public function __construct(
        PointsBalanceFactory $balanceFactory,
        BalanceResource $balanceResource,
        CollectionFactory $collectionFactory
    ) {
        $this->balanceFactory = $balanceFactory;
        $this->balanceResource = $balanceResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function save(PointsBalanceInterface $balance): PointsBalanceInterface
    {
        $this->balanceResource->save($balance);
        return $balance;
    }

    public function getById(int $balanceId): PointsBalanceInterface
    {
        $balance = $this->balanceFactory->create();
        $this->balanceResource->load($balance, $balanceId);

        if (!$balance->getBalanceId()) {
            throw new NoSuchEntityException(__('Balance not found.'));
        }

        return $balance;
    }

    public function getByCustomerId(int $customerId): PointsBalanceInterface
    {
        $balance = $this->balanceFactory->create();
        $this->balanceResource->loadByCustomerId($balance, $customerId);

        if (!$balance->getBalanceId()) {
            throw new NoSuchEntityException(__('Balance not found for customer.'));
        }

        return $balance;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        // Implement search criteria support
        // We'll skip this for brevity
    }

    public function delete(PointsBalanceInterface $balance): bool
    {
        $this->balanceResource->delete($balance);
        return true;
    }
}
