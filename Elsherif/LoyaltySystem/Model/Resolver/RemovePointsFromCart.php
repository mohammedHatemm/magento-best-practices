<?php
/**
 * GraphQL Mutation Resolver - Remove Loyalty Points from Cart
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Helper\Config;
use Magento\Quote\Api\CartRepositoryInterface;

class RemovePointsFromCart implements ResolverInterface
{
    private GetCartForUser $getCartForUser;
    private PointsManagementInterface $pointsManagement;
    private Config $config;
    private CartRepositoryInterface $cartRepository;

    public function __construct(
        GetCartForUser $getCartForUser,
        PointsManagementInterface $pointsManagement,
        Config $config,
        CartRepositoryInterface $cartRepository
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->pointsManagement = $pointsManagement;
        $this->config = $config;
        $this->cartRepository = $cartRepository;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        if (!$this->config->isEnabled()) {
            throw new GraphQlInputException(__('Loyalty program is currently disabled.'));
        }

        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('Please login to use loyalty points.'));
        }

        $maskedCartId = $args['cart_id'] ?? null;

        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Cart ID is required.'));
        }

        $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
        $customerId = $context->getUserId();

        // Get cart
        $cart = $this->getCartForUser->execute($maskedCartId, $customerId, $storeId);

        // Cancel redemption
        $success = $this->pointsManagement->cancelRedemption((int) $cart->getId());

        if (!$success) {
            throw new GraphQlInputException(__('Failed to remove loyalty points from cart.'));
        }

        // Reload cart
        $cart = $this->cartRepository->get($cart->getId());

        return [
            'success' => true,
            'message' => (string) __('Loyalty points removed successfully.'),
            'cart' => [
                'model' => $cart
            ]
        ];
    }
}
