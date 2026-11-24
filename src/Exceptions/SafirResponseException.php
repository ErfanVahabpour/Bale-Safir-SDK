<?php

namespace EFive\Bale\Safir\Exceptions;

final class SafirResponseException extends SafirException
{
    public function __construct(
        string $message,
        private readonly ?int $httpStatusCode,
        private readonly string $rawResponse,
        private readonly array $responseData = []
    ) {
        parent::__construct($message, $httpStatusCode ?? 0);
    }

    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    public function getRawResponse(): string
    {
        return $this->rawResponse;
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }
}
