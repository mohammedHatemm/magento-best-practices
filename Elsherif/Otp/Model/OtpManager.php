<?php

namespace Elsherif\Otp\Model;

use Elsherif\Otp\Api\OTPManagerInterface;
use Elsherif\Otp\Api\SenderInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class OtpManager implements OtpManagerInterface
{
    private const OTP_LENGTH = 6;
    private const EXPIRY_MINUTES = 5;
    private const MAX_ATTEMPTS = 3;
    public function __construct(
        private OtpFactory $otpFactory,
        private OtpResource  $otpResource,
        private Random  $random,
        private EncryptorInterface $encryptor,
        private DateTime $dateTime,
        private SenderInterface $sender,

    )
    {}
    public function generateAndSend(string $identifier, string $type='email') : bool
    {
        $this->invalidate($identifier);
        $otpCode = $this->random->getRandomString(self::OTP_LENGTH
        , '123456789');

        $expiresAt = $his->dateTime->gmtDate(
            'Y-m-d H:i:s',
            strtotime(
                '+' . self::EXPIRY_MINUTES . ' minutes'
            )
        );
        $otp = $this->otpFactory->create();
        $otp->setIdentifier($identifier)
            ->setOtpCode($this->encryptor->encrypt($otpCode))
            ->setPurpose($type)
            ->setExpiresAt($expiresAt)


    }

}
