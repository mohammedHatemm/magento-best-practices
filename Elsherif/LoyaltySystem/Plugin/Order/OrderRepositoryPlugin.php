<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Plugin\Order;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderSearchResultInterface;

/**
 * Plugin to handle Order extension attributes for loyalty points
 */
class OrderRepositoryPlugin
{
    /**
     * @var OrderExtensionFactory
     */
    private OrderExtensionFactory $orderExtensionFactory;

    /**
     * @param OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * Add extension attributes to order after get
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ): OrderInterface {
        return $this->loadExtensionAttributes($order);
    }

    /**
     * Add extension attributes to orders in list
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResults
     * @return OrderSearchResultInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        OrderSearchResultInterface $searchResults
    ): OrderSearchResultInterface {
        foreach ($searchResults->getItems() as $order) {
            $this->loadExtensionAttributes($order);
        }
        return $searchResults;
    }

    /**
     * Save extension attributes before save
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     * @return array
     */
    public function beforeSave(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ): array {
        $extensionAttributes = $order->getExtensionAttributes();
        
        if ($extensionAttributes) {
            if ($extensionAttributes->getLoyaltyPointsEarned() !== null) {
                $order->setData('loyalty_points_earned', $extensionAttributes->getLoyaltyPointsEarned());
            }
            if ($extensionAttributes->getLoyaltyPointsUsed() !== null) {
                $order->setData('loyalty_points_used', $extensionAttributes->getLoyaltyPointsUsed());
            }
            if ($extensionAttributes->getLoyaltyDiscountAmount() !== null) {
                $order->setData('loyalty_discount_amount', $extensionAttributes->getLoyaltyDiscountAmount());
            }
        }
        
        return [$order];
    }

    /**
     * Load extension attributes from order data
     *
     * @param OrderInterface $order
     * @return OrderInterface
     */
    private function loadExtensionAttributes(OrderInterface $order): OrderInterface
    {
        $extensionAttributes = $order->getExtensionAttributes();
        
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->orderExtensionFactory->create();
        }

        $extensionAttributes->setLoyaltyPointsEarned(
            (int) $order->getData('loyalty_points_earned')
        );
        $extensionAttributes->setLoyaltyPointsUsed(
            (int) $order->getData('loyalty_points_used')
        );
        $extensionAttributes->setLoyaltyDiscountAmount(
            (float) $order->getData('loyalty_discount_amount')
        );

        $order->setExtensionAttributes($extensionAttributes);
        
        return $order;
    }
}
