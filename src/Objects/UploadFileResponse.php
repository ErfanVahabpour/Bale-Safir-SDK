<?php

namespace EFive\Bale\Safir\Objects;

final class UploadFileResponse
{
    public function __construct(
        private readonly array $data,
        private readonly ?int $httpStatusCode = null,
        private readonly string $rawResponse = ''
    ) {
    }

    public function getFileId(): ?string
    {
        $fileId = $this->data['file_id'] ?? null;

        return is_scalar($fileId) ? (string) $fileId : null;
    }

    public function getError(): mixed
    {
        return $this->data['error'] ?? $this->data['error_data'] ?? null;
    }

    public function getErrorInfo(): ?ErrorInfo
    {
        $error = $this->getError();
        if (is_array($error) && array_is_list($error)) {
            $error = $error[0] ?? null;
        }

        if (! is_array($error)) {
            return null;
        }

        return ErrorInfo::fromArray($error);
    }

    public function hasErrors(): bool
    {
        $error = $this->getError();

        return $error !== null && $error !== [] && $error !== '' && $error !== false;
    }

    public function isSuccessful(): bool
    {
        return $this->getFileId() !== null && ! $this->hasErrors();
    }

    public function getHttpStatusCode(): ?int
    {
        return $this->httpStatusCode;
    }

    public function getRawResponse(): string
    {
        return $this->rawResponse;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
