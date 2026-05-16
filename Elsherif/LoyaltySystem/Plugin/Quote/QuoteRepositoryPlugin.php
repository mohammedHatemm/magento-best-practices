<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Plugin\Quote;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartSearchResultsInterface;

/**
 * Plugin to handle Quote extension attributes for loyalty points
 */
class QuoteRepositoryPlugin
{
    /**
     * @var CartExtensionFactory
     */
    private CartExtensionFactory $cartExtensionFactory;

    /**
     * @param CartExtensionFactory $cartExtensionFactory
     */
    public function __construct(
        CartExtensionFactory $cartExtensionFactory
    ) {
        $this->cartExtensionFactory = $cartExtensionFactory;
    }

    /**
     * Add extension attributes to quote after get
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return CartInterface
     */
    public function afterGet(
        CartRepositoryInterface $subject,
        CartInterface $quote
    ): CartInterface {
        return $this->loadExtensionAttributes($quote);
    }

    /**
     * Add extension attributes to quote after getActive
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return CartInterface
     */
    public function afterGetActive(
        CartRepositoryInterface $subject,
        CartInterface $quote
    ): CartInterface {
        return $this->loadExtensionAttributes($quote);
    }

    /**
     * Add extension attributes to quote after getActiveForCustomer
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return CartInterface
     */
    public function afterGetActiveForCustomer(
        CartRepositoryInterface $subject,
        CartInterface $quote
    ): CartInterface {
        return $this->loadExtensionAttributes($quote);
    }

    /**
     * Add extension attributes to quotes in list
     *
     * @param CartRepositoryInterface $subject
     * @param CartSearchResultsInterface $searchResults
     * @return CartSearchResultsInterface
     */
    public function afterGetList(
        CartRepositoryInterface $subject,
        CartSearchResultsInterface $searchResults
    ): CartSearchResultsInterface {
        foreach ($searchResults->getItems() as $quote) {
            $this->loadExtensionAttributes($quote);
        }
        return $searchResults;
    }

    /**
     * Save extension attributes before save
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return array
     */
    public function beforeSave(
        CartRepositoryInterface $subject,
        CartInterface $quote
    ): array {
        $extensionAttributes = $quote->getExtensionAttributes();
        
        if ($extensionAttributes) {
            if ($extensionAttributes->getLoyaltyPointsUsed() !== null) {
                $quote->setData('loyalty_points_used', $extensionAttributes->getLoyaltyPointsUsed());
            }
            if ($extensionAttributes->getLoyaltyDiscountAmount() !== null) {
                $quote->setData('loyalty_discount_amount', $extensionAttributes->getLoyaltyDiscountAmount());
            }
        }
        
        return [$quote];
    }

    /**
     * Load extension attributes from quote data
     *
     * @param CartInterface $quote
     * @return CartInterface
     */
    private function loadExtensionAttributes(CartInterface $quote): CartInterface
    {
        $extensionAttributes = $quote->getExtensionAttributes();
        
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->cartExtensionFactory->create();
        }

        $extensionAttributes->setLoyaltyPointsUsed(
            (int) $quote->getData('loyalty_points_used')
        );
        $extensionAttributes->setLoyaltyDiscountAmount(
            (float) $quote->getData('loyalty_discount_amount')
        );

        $quote->setExtensionAttributes($extensionAttributes);
        
        return $quote;
    }
}
