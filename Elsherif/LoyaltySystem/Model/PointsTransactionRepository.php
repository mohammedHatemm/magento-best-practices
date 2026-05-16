<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model;

use Elsherif\LoyaltySystem\Api\PointsTransactionRepositoryInterface;
use Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface;
use Elsherif\LoyaltySystem\Model\PointsTransactionFactory;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction as TransactionResource;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class PointsTransactionRepository implements PointsTransactionRepositoryInterface
{
    private $transactionFactory;
    private $transactionResource;
    private $collectionFactory;

    public function __construct(
        PointsTransactionFactory $transactionFactory,
        TransactionResource $transactionResource,
        CollectionFactory $collectionFactory
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->transactionResource = $transactionResource;
        $this->collectionFactory = $collectionFactory;
    }

    public function save(PointsTransactionInterface $transaction): PointsTransactionInterface
    {
        $this->transactionResource->save($transaction);
        return $transaction;
    }

    public function getById(int $transactionId): PointsTransactionInterface
    {
        $transaction = $this->transactionFactory->create();
        $this->transactionResource->load($transaction, $transactionId);

        if (!$transaction->getTransactionId()) {
            throw new NoSuchEntityException(__('Transaction not found.'));
        }

        return $transaction;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        // Implement search criteria support
        // We'll skip this for brevity
    }

    public function delete(PointsTransactionInterface $transaction): bool
    {
        $this->transactionResource->delete($transaction);
        return true;
    }
}
