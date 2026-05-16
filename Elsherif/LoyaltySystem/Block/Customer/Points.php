<?php
/**
 * Points Block (ViewModel pattern)
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Model\Config;
use Elsherif\LoyaltySystem\Helper\Data as DataHelper;

class Points extends Template
{
    private $customerSession;
    private $pointsManagement;
    private $config;
    private $dataHelper;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        PointsManagementInterface $pointsManagement,
        Config $config,
        DataHelper $dataHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->pointsManagement = $pointsManagement;
        $this->config = $config;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get customer points balance
     *
     * @return \Elsherif\LoyaltySystem\Api\Data\PointsBalanceInterface
     */
    public function getPointsBalance()
    {
        $customerId = (int) $this->customerSession->getCustomerId();
        return $this->pointsManagement->getBalance($customerId);
    }

    /**
     * Get points value in currency
     *
     * @param int $points
     * @return float
     */
    public function getPointsValue(int $points): float
    {
        return $this->dataHelper->calculatePointsValue($points);
    }

    /**
     * Format price
     *
     * @param float $amount
     * @return string
     */
    public function formatPrice(float $amount): string
    {
        return $this->dataHelper->formatPrice($amount);
    }

    /**
     * Get earning rate
     *
     * @return float
     */
    public function getEarnRate(): float
    {
        return $this->config->getEarnRate();
    }

    /**
     * Get redemption rate
     *
     * @return float
     */
    public function getRedeemRate(): float
    {
        return $this->config->getRedeemRate();
    }

    /**
     * Get expiration days
     *
     * @return int
     */
    public function getExpirationDays(): int
    {
        return $this->config->getExpirationDays();
    }

    /**
     * Get transactions URL
     *
     * @return string
     */
    public function getTransactionsUrl(): string
    {
        return $this->getUrl('loyalty/customer/transactions');
    }
}
