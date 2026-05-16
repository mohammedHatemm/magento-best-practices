<?php
/**
 * Points Management Service
 * Main business logic implementation
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model;

use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface;
use Elsherif\LoyaltySystem\Api\Data\PointsTransactionInterface;
use Elsherif\LoyaltySystem\Api\Data\RedemptionResultInterface;
use Elsherif\LoyaltySystem\Api\PointsBalanceRepositoryInterface;
use Elsherif\LoyaltySystem\Model\PointsBalanceFactory;
use Elsherif\LoyaltySystem\Model\PointsTransactionFactory;
use Elsherif\LoyaltySystem\Model\ResourceModel\PointsTransaction as TransactionResource;
use Elsherif\LoyaltySystem\Model\Config;
use Elsherif\LoyaltySystem\Model\PointsCalculator;
use Elsherif\LoyaltySystem\Api\Data\RedemptionResultInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class PointsManagement implements PointsManagementInterface
{
    /**
     * @var PointsBalanceRepositoryInterface
     */
    private $balanceRepository;

    /**
     * @var PointsBalanceFactory
     */
    private $balanceFactory;

    /**
     * @var PointsTransactionFactory
     */
    private $transactionFactory;

    /**
     * @var TransactionResource
     */
    private $transactionResource;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PointsCalculator
     */
    private $calculator;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var RedemptionResultInterfaceFactory
     */
    private $redemptionResultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     */
    public function __construct(
        PointsBalanceRepositoryInterface $balanceRepository,
        PointsBalanceFactory $balanceFactory,
        PointsTransactionFactory $transactionFactory,
        TransactionResource $transactionResource,
        Config $config,
        PointsCalculator $calculator,
        CartRepositoryInterface $quoteRepository,
        RedemptionResultInterfaceFactory $redemptionResultFactory,
        LoggerInterface $logger
    ) {
        $this->balanceRepository = $balanceRepository;
        $this->balanceFactory = $balanceFactory;
        $this->transactionFactory = $transactionFactory;
        $this->transactionResource = $transactionResource;
        $this->config = $config;
        $this->calculator = $calculator;
        $this->quoteRepository = $quoteRepository;
        $this->redemptionResultFactory = $redemptionResultFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function addPoints(
        int $customerId,
        int $points,
        string $action,
        ?int $referenceId = null,
        ?string $expiresAt = null,
        ?string $comment = null
    ): bool {
        try {
            // Get or create balance
            try {
                $balance = $this->balanceRepository->getByCustomerId($customerId);
            } catch (NoSuchEntityException $e) {
                $balance = $this->balanceFactory->create();
                $balance->setCustomerId($customerId);
                $balance->setPoints(0);
                $balance->setLifetimePoints(0);
                $balance->setPointsSpent(0);
            }

            // Update balance
            $currentPoints = $balance->getPoints();
            $newPoints = $currentPoints + $points;
            
            $balance->setPoints($newPoints);
            $balance->setLifetimePoints($balance->getLifetimePoints() + $points);

            // Save balance
            $this->balanceRepository->save($balance);

            // Create transaction record
            $transaction = $this->transactionFactory->create();
            $transaction->setCustomerId($customerId);
            $transaction->setPoints($points);
            $transaction->setBalanceAfter($newPoints);
            $transaction->setAction($action);
            $transaction->setReferenceId($referenceId);
            $transaction->setReferenceType($this->getReferenceType($referenceId));
            $transaction->setExpiresAt($expiresAt);
            $transaction->setComment($comment);

            $this->transactionResource->save($transaction);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error adding points: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function deductPoints(
        int $customerId,
        int $points,
        string $action,
        ?int $referenceId = null,
        ?string $comment = null
    ): bool {
        try {
            // Get balance
            $balance = $this->balanceRepository->getByCustomerId($customerId);

            // Check sufficient balance
            if ($balance->getPoints() < $points) {
                throw new LocalizedException(__('Insufficient points balance.'));
            }

            // Update balance
            $currentPoints = $balance->getPoints();
            $newPoints = $currentPoints - $points;
            
            $balance->setPoints($newPoints);
            $balance->setPointsSpent($balance->getPointsSpent() + $points);

            // Save balance
            $this->balanceRepository->save($balance);

            // Create transaction record (negative points)
            $transaction = $this->transactionFactory->create();
            $transaction->setCustomerId($customerId);
            $transaction->setPoints(-$points); // Negative!
            $transaction->setBalanceAfter($newPoints);
            $transaction->setAction($action);
            $transaction->setReferenceId($referenceId);
            $transaction->setReferenceType($this->getReferenceType($referenceId));
            $transaction->setComment($comment);

            $this->transactionResource->save($transaction);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error deducting points: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getBalance(int $customerId): PointsBalanceInterface
    {
        try {
            return $this->balanceRepository->getByCustomerId($customerId);
        } catch (NoSuchEntityException $e) {
            // Create empty balance
            $balance = $this->balanceFactory->create();
            $balance->setCustomerId($customerId);
            $balance->setPoints(0);
            $balance->setLifetimePoints(0);
            $balance->setPointsSpent(0);
            
            return $balance;
        }
    }

    /**
     * @inheritDoc
     */
    public function redeemPoints(int $quoteId, int $points): RedemptionResultInterface
    {
        $result = $this->redemptionResultFactory->create();

        try {
            // Validate
            if (!$this->config->isEnabled()) {
                throw new LocalizedException(__('Loyalty system is disabled.'));
            }

            if ($points < $this->config->getMinPointsToRedeem()) {
                throw new LocalizedException(
                    __('Minimum %1 points required.', $this->config->getMinPointsToRedeem())
                );
            }

            // Get quote
            $quote = $this->quoteRepository->getActive($quoteId);

            if (!$quote->getCustomerId()) {
                throw new LocalizedException(__('Guest customers cannot redeem points.'));
            }

            // Get balance
            $balance = $this->getBalance((int) $quote->getCustomerId());

            if ($balance->getPoints() < $points) {
                throw new LocalizedException(__('Insufficient points.'));
            }

            // Calculate discount
            $discount = $this->calculator->calculateDiscount($points);

            // Apply to quote columns directly (for Total Collector)
            $quote->setData('loyalty_points_used', $points);
            $quote->setData('loyalty_discount_amount', $discount);

            // Also set extension attributes for API compatibility
            $extensionAttributes = $quote->getExtensionAttributes();
            if ($extensionAttributes) {
                $extensionAttributes->setLoyaltyPointsUsed($points);
                $extensionAttributes->setLoyaltyDiscountAmount($discount);
                $quote->setExtensionAttributes($extensionAttributes);
            }

            // Trigger totals recalculation
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();

            // Save quote
            $this->quoteRepository->save($quote);

            // Set result
            $result->setSuccess(true);
            $result->setMessage((string) __('Points applied successfully.'));
            $result->setPointsUsed($points);
            $result->setDiscountAmount($discount);
            $result->setNewBalance($balance->getPoints()); // Will be deducted on order placement

        } catch (\Exception $e) {
            $result->setSuccess(false);
            $result->setMessage($e->getMessage());
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function cancelRedemption(int $quoteId): bool
    {
        try {
            $quote = $this->quoteRepository->getActive($quoteId);

            // Clear quote columns
            $quote->setData('loyalty_points_used', 0);
            $quote->setData('loyalty_discount_amount', 0.0);

            // Clear extension attributes
            $extensionAttributes = $quote->getExtensionAttributes();
            if ($extensionAttributes) {
                $extensionAttributes->setLoyaltyPointsUsed(0);
                $extensionAttributes->setLoyaltyDiscountAmount(0.0);
                $quote->setExtensionAttributes($extensionAttributes);
            }

            // Recalculate totals
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();

            $this->quoteRepository->save($quote);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error cancelling redemption: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get reference type from ID
     *
     * @param int|null $referenceId
     * @return string|null
     */
    private function getReferenceType(?int $referenceId): ?string
    {
        return $referenceId ? 'order' : null;
    }
}
