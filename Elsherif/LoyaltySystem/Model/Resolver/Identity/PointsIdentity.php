<?php
/**
 * GraphQL Cache Identity for Points Data
 */
declare(strict_types=1);

namespace Elsherif\LoyaltySystem\Model\Resolver\Identity;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

class PointsIdentity implements IdentityInterface
{
    private string $cacheTag = 'loyalty_points';

    /**
     * Get identity tags from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $ids = [];
        
        if (isset($resolvedData['customer_id'])) {
            $ids[] = sprintf('%s_%s', $this->cacheTag, $resolvedData['customer_id']);
        }
        
        if (empty($ids)) {
            return [$this->cacheTag];
        }
        
        return $ids;
    }
}
