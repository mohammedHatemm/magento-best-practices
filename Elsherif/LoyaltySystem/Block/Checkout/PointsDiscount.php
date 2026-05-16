<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Elsherif\LoyaltySystem\Model\Config;
use Elsherif\LoyaltySystem\Api\PointsBalanceRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Block for checkout loyalty points discount component
 */
class PointsDiscount extends Template
{
    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var PointsBalanceRepositoryInterface
     */
    private PointsBalanceRepositoryInterface $pointsBalanceRepository;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param Config $config
     * @param PointsBalanceRepositoryInterface $pointsBalanceRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        Config $config,
        PointsBalanceRepositoryInterface $pointsBalanceRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->config = $config;
        $this->pointsBalanceRepository = $pointsBalanceRepository;
    }

    /**
     * Check if loyalty system is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn(): bool
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * Get current customer ID
     *
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        return $this->customerSession->getCustomerId() 
            ? (int) $this->customerSession->getCustomerId() 
            : null;
    }

    /**
     * Get customer's available points
     *
     * @return int
     */
    public function getAvailablePoints(): int
    {
        if (!$this->isCustomerLoggedIn()) {
            return 0;
        }

        try {
            $balance = $this->pointsBalanceRepository->getByCustomerId(
                (int) $this->customerSession->getCustomerId()
            );
            return $balance->getPoints();
        } catch (NoSuchEntityException $e) {
            return 0;
        }
    }

    /**
     * Get redemption rate (points per currency unit)
     *
     * @return int
     */
    public function getRedemptionRate(): int
    {
        return $this->config->getRedemptionRate();
    }

    /**
     * Get minimum points for redemption
     *
     * @return int
     */
    public function getMinimumPoints(): int
    {
        return $this->config->getMinimumPointsForRedemption();
    }

    /**
     * Check if customer can use points (has minimum required)
     *
     * @return bool
     */
    public function canUsePoints(): bool
    {
        return $this->getAvailablePoints() >= $this->getMinimumPoints();
    }

    /**
     * Get JS component configuration
     *
     * @return array
     */
    public function getJsConfig(): array
    {
        return [
            'component' => 'Elsherif_LoyaltySystem/js/view/checkout/points-discount',
            'config' => [
                'isEnabled' => $this->isEnabled(),
                'isLoggedIn' => $this->isCustomerLoggedIn(),
                'customerId' => $this->getCustomerId(),
                'availablePoints' => $this->getAvailablePoints(),
                'redemptionRate' => $this->getRedemptionRate(),
                'minimumPoints' => $this->getMinimumPoints(),
                'canUsePoints' => $this->canUsePoints()
            ]
        ];
    }

    /**
     * Get serialized JS config
     *
     * @return string
     */
    public function getSerializedConfig(): string
    {
        return json_encode($this->getJsConfig());
    }
}
