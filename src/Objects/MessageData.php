<?php

namespace EFive\Bale\Safir\Objects;

use JsonSerializable;

final class MessageData implements JsonSerializable
{
    public function __construct(
        private readonly ?Message $message = null,
        private readonly ?OTPMessage $otpMessage = null,
        private readonly bool $isSecure = false
    ) {
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function getOtpMessage(): ?OTPMessage
    {
        return $this->otpMessage;
    }

    public function isSecure(): bool
    {
        return $this->isSecure;
    }

    /** @return array{message?:array, otp_message?:array, is_secure:bool} */
    public function toArray(): array
    {
        $data = [];

        if ($this->message !== null) {
            $data['message'] = $this->message->toArray();
        }

        if ($this->otpMessage !== null) {
            $data['otp_message'] = $this->otpMessage->toArray();
        }

        $data['is_secure'] = $this->isSecure;

        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
