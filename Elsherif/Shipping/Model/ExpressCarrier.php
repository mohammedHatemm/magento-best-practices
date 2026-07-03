<?php

namespace Elsherif\Shipping\Model;

use Elsherif\Shipping\Api\ShippingInterface;


class ExpressCarrier implements ShippingInterface
{
    private const BASE_PRICE = 15.0 ;
    private const PRICE_PER_KG = 2.0;

    public function getRate(float $weight): float
    {
        $price = self::BASE_PRICE + ($weight * self::PRICE_PER_KG);
        return $price;

    }

    public function getCarrierName(): string
    {
        return 'Express';
    }


}
