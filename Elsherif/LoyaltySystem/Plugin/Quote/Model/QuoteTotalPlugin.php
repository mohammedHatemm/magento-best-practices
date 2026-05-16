<?php
/**
 * Plugin to sync loyalty data with extension attributes
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Plugin\Quote\Model;

use Magento\Quote\Model\Quote;

class QuoteTotalPlugin
{
    /**
     * Sync quote data columns with extension attributes after totals collection
     * The actual discount is applied by the Total Collector
     */
    public function afterCollectTotals(Quote $subject, Quote $result): Quote
    {
        // Sync from quote columns to extension attributes
        $loyaltyPointsUsed = (int) $subject->getData('loyalty_points_used');
        $loyaltyDiscountAmount = (float) $subject->getData('loyalty_discount_amount');

        if ($loyaltyPointsUsed > 0) {
            $extensionAttributes = $subject->getExtensionAttributes();
            if ($extensionAttributes) {
                $extensionAttributes->setLoyaltyPointsUsed($loyaltyPointsUsed);
                $extensionAttributes->setLoyaltyDiscountAmount($loyaltyDiscountAmount);
                $subject->setExtensionAttributes($extensionAttributes);
            }
        }

        return $result;
    }
}
