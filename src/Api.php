<?php

namespace EFive\Bale\Safir;

use EFive\Bale\Safir\Exceptions\InvalidRequestException;
use EFive\Bale\Safir\Exceptions\MissingBotIdException;
use EFive\Bale\Safir\FileUpload\InputFile;
use EFive\Bale\Safir\Objects\Message;
use EFive\Bale\Safir\Objects\MessageData;
use EFive\Bale\Safir\Objects\OTPMessage;
use EFive\Bale\Safir\Objects\ReplyMarkup;
use EFive\Bale\Safir\Objects\SendMessageResponse;
use EFive\Bale\Safir\Objects\UploadFileResponse;
use GuzzleHttp\ClientInterface;

/**
 * High-level client for the documented Bale Safir API.
 */
final class Api
{
    public const API_ACCESS_KEY_ENV_NAME = 'BALE_SAFIR_API_ACCESS_KEY';
    public const BOT_ID_ENV_NAME = 'BALE_SAFIR_BOT_ID';

    private readonly SafirClient $client;
    private readonly ?int $botId;

    public function __construct(
        ?string $apiAccessKey = null,
        int|string|null $botId = null,
        ?ClientInterface $httpClient = null,
        string $baseUrl = SafirClient::BASE_URL,
        int $timeout = 30,
        int $connectTimeout = 10
    ) {
        $apiAccessKey ??= self::environmentValue(self::API_ACCESS_KEY_ENV_NAME);
        $botId ??= self::environmentValue(self::BOT_ID_ENV_NAME);
        $this->botId = self::parseBotId($botId);

        $this->client = new SafirClient($apiAccessKey, $httpClient, $baseUrl, $timeout, $connectTimeout);
    }

    /**
     * Send a documented Safir message payload.
     *
     * @param array{phone_number:string, message_data:array|MessageData, bot_id?:int|string, request_id?:string} $params
     */
    public function sendMessage(array $params): SendMessageResponse
    {
        if (! isset($params['phone_number']) || ! is_string($params['phone_number']) || trim($params['phone_number']) === '') {
            throw new InvalidRequestException('A non-empty phone_number string is required.');
        }

        $params['phone_number'] = self::normalizePhoneNumber(trim($params['phone_number']));

        if (($params['message_data'] ?? null) instanceof MessageData) {
            $params['message_data'] = $params['message_data']->toArray();
        }

        if (! isset($params['message_data']) || ! is_array($params['message_data'])) {
            throw new InvalidRequestException('message_data must be an array.');
        }

        if (! isset($params['bot_id']) || $params['bot_id'] === '') {
            if ($this->botId === null) {
                throw new MissingBotIdException();
            }

            $params['bot_id'] = $this->botId;
        } else {
            try {
                $params['bot_id'] = self::parseBotId($params['bot_id']);
            } catch (\InvalidArgumentException $exception) {
                throw new InvalidRequestException('bot_id must be a positive integer.', 0, $exception);
            }
        }

        return $this->client->sendMessage($params);
    }

    /**
     * Send an OTP message. A random six-digit code and request ID are generated when omitted.
     */
    public function sendOtp(
        string $phoneNumber,
        string|int|null $code = null,
        bool $isSecure = false,
        ?string $requestId = null
    ): SendMessageResponse {
        $otp = $code ?? random_int(100000, 999999);

        return $this->sendMessage([
            'phone_number' => $phoneNumber,
            'request_id' => self::resolveRequestId($requestId),
            'message_data' => new MessageData(
                otpMessage: new OTPMessage($otp),
                isSecure: $isSecure
            ),
        ]);
    }

    /**
     * Send a text or captioned file message, optionally with inline buttons.
     */
    public function sendText(
        string $phoneNumber,
        string $text,
        ReplyMarkup|array|null $replyMarkup = null,
        bool $isSecure = false,
        ?string $fileId = null,
        ?string $requestId = null
    ): SendMessageResponse {
        return $this->sendMessage([
            'phone_number' => $phoneNumber,
            'request_id' => self::resolveRequestId($requestId),
            'message_data' => new MessageData(
                message: new Message(
                    text: $text,
                    fileId: $fileId,
                    replyMarkup: self::normalizeReplyMarkup($replyMarkup)
                ),
                isSecure: $isSecure
            ),
        ]);
    }

    /**
     * Send a secure text or captioned file message, optionally with inline buttons.
     */
    public function sendSecureText(
        string $phoneNumber,
        string $text,
        ReplyMarkup|array|null $replyMarkup = null,
        ?string $fileId = null,
        ?string $requestId = null
    ): SendMessageResponse {
        return $this->sendText($phoneNumber, $text, $replyMarkup, true, $fileId, $requestId);
    }

    /**
     * Upload a local path or InputFile to Safir and return its file_id.
     */
    public function uploadFile(InputFile|string $file, ?string $filename = null, ?string $mimeType = null): UploadFileResponse
    {
        if (is_string($file)) {
            $file = InputFile::fromPath($file, $filename, $mimeType);
        }

        return $this->client->uploadFile($file);
    }

    private static function environmentValue(string $name): ?string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            $value = $_ENV[$name] ?? $_SERVER[$name] ?? null;
        }

        return is_string($value) && $value !== '' ? $value : null;
    }

    private static function parseBotId(mixed $botId): ?int
    {
        if ($botId === null || $botId === '') {
            return null;
        }

        if (! is_int($botId) && ! is_string($botId)) {
            throw new \InvalidArgumentException('Safir bot_id must be a positive integer.');
        }

        $validated = filter_var($botId, FILTER_VALIDATE_INT);
        if ($validated === false || $validated < 1) {
            throw new \InvalidArgumentException('Safir bot_id must be a positive integer.');
        }

        return $validated;
    }

    private static function normalizePhoneNumber(string $phoneNumber): string
    {
        // Safir requires Iranian numbers in international format. Convert local mobile form (09xxxxxxxxx).
        if (preg_match('/\\A09\\d{9}\\z/', $phoneNumber) === 1) {
            return '98'.substr($phoneNumber, 1);
        }

        return $phoneNumber;
    }

    private static function normalizeReplyMarkup(ReplyMarkup|array|null $replyMarkup): ?ReplyMarkup
    {
        if ($replyMarkup === null || $replyMarkup instanceof ReplyMarkup) {
            return $replyMarkup;
        }

        return ReplyMarkup::fromArray($replyMarkup);
    }

    private static function resolveRequestId(?string $requestId): string
    {
        return $requestId === null || $requestId === '' ? bin2hex(random_bytes(16)) : $requestId;
    }
}
