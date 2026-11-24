<?php

namespace EFive\Bale\Safir\Objects;

use InvalidArgumentException;
use JsonSerializable;

final class OTPMessage implements JsonSerializable
{
    private readonly string $otp;

    public function __construct(string|int $otp)
    {
        $otp = (string) $otp;

        if (preg_match('/\\A\\d+\\z/', $otp) !== 1) {
            throw new InvalidArgumentException('OTP values must contain digits only.');
        }

        $this->otp = $otp;
    }

    public function getOtp(): string
    {
        return $this->otp;
    }

    /** @return array{otp:string} */
    public function toArray(): array
    {
        return ['otp' => $this->otp];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
