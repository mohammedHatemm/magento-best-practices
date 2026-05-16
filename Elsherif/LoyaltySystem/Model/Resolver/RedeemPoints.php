<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class RedeemPoints implements ResolverInterface
{
    private $pointsManagement;
    private $quoteRepository;

    public function __construct(
        PointsManagementInterface $pointsManagement,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->pointsManagement = $pointsManagement;
        $this->quoteRepository = $quoteRepository;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('Customer not authenticated.'));
        }

        if (!isset($args['input']['cart_id']) || !isset($args['input']['points'])) {
            throw new GraphQlInputException(__('Required parameters missing.'));
        }

        $cartId = $args['input']['cart_id'];
        $points = (int) $args['input']['points'];

        // Get quote ID from masked cart ID
        $quote = $this->quoteRepository->getActive($cartId);
        
        $result = $this->pointsManagement->redeemPoints((int)$quote->getId(), $points);

        return [
            'success' => $result->getSuccess(),
            'message' => $result->getMessage(),
            'points_used' => $result->getPointsUsed(),
            'discount_amount' => $result->getDiscountAmount(),
            'new_balance' => $result->getNewBalance()
        ];
    }
}
