<?php
/**
 * Quote Total Collector for Loyalty Points Discount
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Total\Quote;

use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Elsherif\LoyaltySystem\Model\Config;

class LoyaltyDiscount extends AbstractTotal
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->setCode('loyalty_discount');
    }

    /**
     * Collect loyalty discount
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): self {
        parent::collect($quote, $shippingAssignment, $total);

        if (!$this->config->isEnabled()) {
            return $this;
        }

        $items = $shippingAssignment->getItems();
        if (!count($items)) {
            return $this;
        }

        // Get loyalty discount from quote
        $loyaltyPointsUsed = (int) $quote->getData('loyalty_points_used');
        $loyaltyDiscount = (float) $quote->getData('loyalty_discount_amount');

        if ($loyaltyPointsUsed <= 0 || $loyaltyDiscount <= 0) {
            return $this;
        }

        // Apply discount
        $total->addTotalAmount($this->getCode(), -$loyaltyDiscount);
        $total->addBaseTotalAmount($this->getCode(), -$loyaltyDiscount);

        // Set on quote for order transfer
        $quote->setData('loyalty_points_used', $loyaltyPointsUsed);
        $quote->setData('loyalty_discount_amount', $loyaltyDiscount);

        return $this;
    }

    /**
     * Fetch totals for display
     */
    public function fetch(Quote $quote, Total $total): ?array
    {
        $loyaltyDiscount = (float) $quote->getData('loyalty_discount_amount');

        if ($loyaltyDiscount <= 0) {
            return null;
        }

        return [
            'code' => $this->getCode(),
            'title' => __('Loyalty Points Discount'),
            'value' => -$loyaltyDiscount
        ];
    }

    /**
     * Get label
     */
    public function getLabel(): \Magento\Framework\Phrase
    {
        return __('Loyalty Points Discount');
    }
}
