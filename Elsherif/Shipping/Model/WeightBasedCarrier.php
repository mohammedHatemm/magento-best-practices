<?php

namespace Elsherif\Shipping\Model;
use Elsherif\Shipping\Api\ShippingInterface;
class WeightBasedCarrier implements ShippingInterface
{
    private const PRICE_PER_KG = 0.50;
    private const MIN_RATE = 2.0;

    public function getRate(float $weight): float
    {
        $calculateRate = $weight * self::PRICE_PER_KG;
        return max($calculateRate, self::MIN_RATE);
    }
    public function getCarrierName(): string
    {
        return 'WeightBasedCarrier';
    }


}
