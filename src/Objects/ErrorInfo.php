<?php

namespace EFive\Bale\Safir\Objects;

final class ErrorInfo
{
    public const INTERNAL_SERVER_ERROR = 2;
    public const RATE_LIMIT_EXCEEDED = 3;
    public const INVALID_INPUT = 4;
    public const INVALID_PHONE = 8;
    public const NOT_BALE_USER = 17;
    public const PAYMENT_REQUIRED = 20;
    public const MAXIMUM_CONTACT_LIMIT_REACHED = 21;

    private const CODE_NAMES = [
        self::INTERNAL_SERVER_ERROR => 'InternalServerError',
        self::RATE_LIMIT_EXCEEDED => 'RateLimitExceeded',
        self::INVALID_INPUT => 'InvalidInput',
        self::INVALID_PHONE => 'InvalidPhone',
        self::NOT_BALE_USER => 'NotBaleUser',
        self::PAYMENT_REQUIRED => 'PaymentRequired',
        self::MAXIMUM_CONTACT_LIMIT_REACHED => 'MaximumContactLimitReached',
    ];

    public function __construct(
        private readonly ?string $phoneNumber,
        private readonly ?int $code,
        private readonly ?string $description,
        private readonly array $rawData = []
    ) {
    }

    public static function fromArray(array $data): self
    {
        $code = $data['code'] ?? null;

        return new self(
            isset($data['phone_number']) ? (string) $data['phone_number'] : null,
            is_numeric($code) ? (int) $code : null,
            isset($data['description']) ? (string) $data['description'] : null,
            $data
        );
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function getCodeName(): ?string
    {
        return $this->code === null ? null : (self::CODE_NAMES[$this->code] ?? null);
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return $this->rawData;
    }
}
