<?php

namespace Elsherif\Otp\Api;

use Magento\Tests\NamingConvention\true\string;

interface SenderInterface
{

    /**
     * @param string $recipient
     * @param  string $otp
     * @return bool
     *
     *
     * */

    public function send(string $recipient, string $otp) : bool;

/**
 * @return string
 *
 *
 * */
    public function getType() : string;


}
