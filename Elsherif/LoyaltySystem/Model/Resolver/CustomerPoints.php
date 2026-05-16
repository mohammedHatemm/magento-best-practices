<?php
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Elsherif\LoyaltySystem\Api\PointsManagementInterface;

class CustomerPoints implements ResolverInterface
{
    private $pointsManagement;

    public function __construct(PointsManagementInterface $pointsManagement)
    {
        $this->pointsManagement = $pointsManagement;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('Customer not authenticated.'));
        }

        $balance = $this->pointsManagement->getBalance($context->getUserId());

        return [
            'balance_id' => $balance->getBalanceId(),
            'customer_id' => $balance->getCustomerId(),
            'points' => $balance->getPoints(),
            'lifetime_points' => $balance->getLifetimePoints(),
            'points_spent' => $balance->getPointsSpent(),
            'updated_at' => $balance->getUpdatedAt()
        ];
    }
}
