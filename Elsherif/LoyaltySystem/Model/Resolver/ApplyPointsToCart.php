<?php
/**
 * GraphQL Mutation Resolver - Apply Loyalty Points to Cart
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Elsherif\LoyaltySystem\Helper\Config;
use Magento\Quote\Api\CartRepositoryInterface;

class ApplyPointsToCart implements ResolverInterface
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

        $maskedCartId = $args['input']['cart_id'] ?? null;
        $pointsToUse = (int) ($args['input']['points'] ?? 0);

        if (!$maskedCartId) {
            throw new GraphQlInputException(__('Cart ID is required.'));
        }

        if ($pointsToUse <= 0) {
            throw new GraphQlInputException(__('Points must be greater than zero.'));
        }

        $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
        $customerId = $context->getUserId();

        // Get cart
        $cart = $this->getCartForUser->execute($maskedCartId, $customerId, $storeId);

        // Apply points
        $result = $this->pointsManagement->redeemPoints((int) $cart->getId(), $pointsToUse);

        if (!$result->getSuccess()) {
            throw new GraphQlInputException(__($result->getMessage()));
        }

        // Reload cart
        $cart = $this->cartRepository->get($cart->getId());

        return [
            'success' => true,
            'message' => $result->getMessage(),
            'cart' => [
                'model' => $cart
            ],
            'points_used' => $result->getPointsUsed(),
            'discount_amount' => $result->getDiscountAmount(),
            'remaining_balance' => $result->getNewBalance()
        ];
    }
}
