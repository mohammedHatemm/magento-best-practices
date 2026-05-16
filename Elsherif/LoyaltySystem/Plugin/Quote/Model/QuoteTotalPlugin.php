<?php
/**
 * Plugin to Apply Loyalty Discount to Quote Totals
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Plugin\Quote\Model;

use Magento\Quote\Model\Quote;
use Elsherif\LoyaltySystem\Model\PointsCalculator;

class QuoteTotalPlugin
{
    private $calculator;

    public function __construct(PointsCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Apply loyalty discount after totals collection
     *
     * @param Quote $subject
     * @param Quote $result
     * @return Quote
     */
    public function afterCollectTotals(Quote $subject, $result)
    {
        $extensionAttributes = $subject->getExtensionAttributes();
        
        if (!$extensionAttributes) {
            return $result;
        }

        $pointsUsed = $extensionAttributes->getLoyaltyPointsUsed();
        
        if (!$pointsUsed || $pointsUsed <= 0) {
            return $result;
        }

        // Calculate discount
        $discount = $this->calculator->calculateDiscount($pointsUsed);

        // Apply discount
        $subject->setSubtotal($subject->getSubtotal() - $discount);
        $subject->setBaseSubtotal($subject->getBaseSubtotal() - $discount);
        $subject->setGrandTotal($subject->getGrandTotal() - $discount);
        $subject->setBaseGrandTotal($subject->getBaseGrandTotal() - $discount);

        // Store discount amount
        $extensionAttributes->setLoyaltyDiscountAmount($discount);
        $subject->setExtensionAttributes($extensionAttributes);

        return $result;
    }
}
