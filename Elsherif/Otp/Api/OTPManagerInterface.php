<?php

namespace Elsherif\Otp\Api;

interface OTPManagerInterface
{

    /**
     *
     *
     * */
    public function generateAndSend(string $identifier, string $type='email') : bool;
    /**
     *
     * */
    public function verify(string $identifier, string $otpCode) : bool;

    /**
     *
     *
    */
    public function invalidate(string $identifier) : void;

}
