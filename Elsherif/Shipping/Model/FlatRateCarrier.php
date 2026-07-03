<?php

namespace Elsherif\Shipping\Model;
use Elsherif\Shipping\Api\ShippingInterface;

class FlatRateCarrier implements ShippingInterface
{
    /**
     *
     */
    public const FLAT_RATE = 5.66;

    /**
     * @param float $weight
     * @return float
     */
    public function getRate(float $weight): float
    {
        return self::FLAT_RATE;
    }

    /**
     * @return string
     * @inheirtDoc
     */
    public function getCarrierName(): string
    {
        return 'flat_rate shipping';
    }

}
