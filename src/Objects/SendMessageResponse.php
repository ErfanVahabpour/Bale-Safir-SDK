<?php

namespace EFive\Bale\Safir\Objects;

final class SendMessageResponse
{
    public function __construct(
        private readonly array $data,
        private readonly ?int $httpStatusCode = null,
        private readonly string $rawResponse = ''
    ) {
    }

    public function getMessageId(): ?string
    {
        $messageId = $this->data['message_id'] ?? null;

        return is_scalar($messageId) ? (string) $messageId : null;
    }

    /**
     * @return list<ErrorInfo>
     */
    public function getErrorData(): array
    {
        $errors = $this->data['error_data'] ?? null;
        if (! is_array($errors) || $errors === []) {
            return [];
        }

        if (! array_is_list($errors)) {
            $errors = [$errors];
        }

        return array_values(array_map(
            static fn (mixed $error): ErrorInfo => ErrorInfo::fromArray(is_array($error) ? $error : []),
            $errors
        ));
    }

    public function getRawErrorData(): mixed
    {
        return $this->data['error_data'] ?? null;
    }

    public function hasErrors(): bool
    {
        $errors = $this->data['error_data'] ?? null;

        return $errors !== null && $errors !== [] && $errors !== '';
    }

    public function isSuccessful(): bool
    {
        return $this->getMessageId() !== null && ! $this->hasErrors();
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
