<?php
/**
 * Plugin to copy loyalty data from quote to order
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Plugin\Quote;

use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Quote\Model\Quote\Address\ToOrder;

class ConvertQuoteToOrder
{
    /**
     * Copy loyalty points data from quote address to order
     */
    public function aroundConvert(
        ToOrder $subject,
        callable $proceed,
        Quote\Address $address,
        array $data = []
    ): OrderInterface {
        /** @var OrderInterface $order */
        $order = $proceed($address, $data);

        $quote = $address->getQuote();

        // Copy loyalty data from quote to order
        $loyaltyPointsUsed = (int) $quote->getData('loyalty_points_used');
        $loyaltyDiscountAmount = (float) $quote->getData('loyalty_discount_amount');

        if ($loyaltyPointsUsed > 0) {
            $order->setData('loyalty_points_used', $loyaltyPointsUsed);
            $order->setData('loyalty_discount_amount', $loyaltyDiscountAmount);
        }

        return $order;
    }
}
