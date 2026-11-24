<?php

namespace EFive\Bale\Safir;

use EFive\Bale\Safir\Exceptions\InvalidRequestException;
use EFive\Bale\Safir\Exceptions\MissingBotIdException;
use EFive\Bale\Safir\FileUpload\InputFile;
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
     * @param array{phone_number:string, message_data:array, bot_id?:int|string, request_id?:string} $params
     */
    public function sendMessage(array $params): SendMessageResponse
    {
        if (! isset($params['phone_number']) || ! is_string($params['phone_number']) || trim($params['phone_number']) === '') {
            throw new InvalidRequestException('A non-empty phone_number string is required.');
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

        // Keep phone-number formatting as supplied; Safir's docs show conflicting examples.
        return $this->client->sendMessage($params);
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
}
