<?php

namespace Elsherif\Shipping\Api;

interface ShippingInterface
{
    /**
     * @return mixed
     */
    public function getRate(float $weight): float;

    /**
     * @return string
     */
    public function getCarrierName(): string;

}
