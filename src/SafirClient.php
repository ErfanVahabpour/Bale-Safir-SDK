<?php

namespace EFive\Bale\Safir;

use EFive\Bale\Safir\Exceptions\MissingApiAccessKeyException;
use EFive\Bale\Safir\Exceptions\SafirNetworkException;
use EFive\Bale\Safir\Exceptions\SafirResponseException;
use EFive\Bale\Safir\FileUpload\InputFile;
use EFive\Bale\Safir\Objects\SendMessageResponse;
use EFive\Bale\Safir\Objects\UploadFileResponse;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

/**
 * Low-level HTTP transport for the Safir REST API.
 */
final class SafirClient
{
    public const BASE_URL = 'https://safir.bale.ai/api/v3';
    public const SEND_MESSAGE_ENDPOINT = 'send_message';
    public const UPLOAD_FILE_ENDPOINT = 'upload_file';

    private readonly ClientInterface $httpClient;
    private readonly string $baseUrl;

    public function __construct(
        private readonly ?string $apiAccessKey,
        ?ClientInterface $httpClient = null,
        string $baseUrl = self::BASE_URL,
        private readonly int $timeout = 30,
        private readonly int $connectTimeout = 10
    ) {
        if ($timeout < 1 || $connectTimeout < 1) {
            throw new \InvalidArgumentException('HTTP timeouts must be greater than zero.');
        }

        $this->baseUrl = rtrim($baseUrl, '/');
        $this->httpClient = $httpClient ?? new Client([
            'timeout' => $timeout,
            'connect_timeout' => $connectTimeout,
            'http_errors' => false,
        ]);
    }

    public function sendMessage(array $payload): SendMessageResponse
    {
        [$data, $response, $rawBody] = $this->postJson(self::SEND_MESSAGE_ENDPOINT, $payload);

        return new SendMessageResponse($data, $response->getStatusCode(), $rawBody);
    }

    public function uploadFile(InputFile $file): UploadFileResponse
    {
        $this->assertApiAccessKey();

        $contents = $file->getContents();
        $part = [
            'name' => 'file',
            'contents' => $contents,
            'filename' => $file->getFilename(),
        ];

        if ($file->getMimeType() !== null) {
            $part['headers'] = ['Content-Type' => $file->getMimeType()];
        }

        try {
            $response = $this->httpClient->request('POST', $this->url(self::UPLOAD_FILE_ENDPOINT), [
                'headers' => $this->headers(),
                'multipart' => [$part],
                'timeout' => $this->timeout,
                'connect_timeout' => $this->connectTimeout,
                'http_errors' => false,
            ]);
        } catch (GuzzleException) {
            throw new SafirNetworkException();
        } finally {
            if ($file->shouldCloseContents() && $contents instanceof \Psr\Http\Message\StreamInterface) {
                $contents->close();
            }
        }

        [$data, $response, $rawBody] = $this->decodeResponse($response, self::UPLOAD_FILE_ENDPOINT);

        return new UploadFileResponse($data, $response->getStatusCode(), $rawBody);
    }

    /**
     * @return array{array, ResponseInterface, string}
     */
    private function postJson(string $endpoint, array $payload): array
    {
        $this->assertApiAccessKey();

        try {
            $response = $this->httpClient->request('POST', $this->url($endpoint), [
                'headers' => $this->headers(),
                'json' => $payload,
                'timeout' => $this->timeout,
                'connect_timeout' => $this->connectTimeout,
                'http_errors' => false,
            ]);
        } catch (GuzzleException) {
            throw new SafirNetworkException();
        }

        return $this->decodeResponse($response, $endpoint);
    }

    /**
     * @return array{array, ResponseInterface, string}
     */
    private function decodeResponse(ResponseInterface $response, string $endpoint): array
    {
        $rawBody = (string) $response->getBody();
        $decoded = [];
        $jsonError = null;

        try {
            $value = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($value) && str_starts_with(ltrim($rawBody), '{')) {
                $decoded = $value;
            } else {
                $jsonError = 'The API returned a JSON value instead of an object.';
            }
        } catch (JsonException) {
            $jsonError = 'The API returned an invalid JSON response.';
        }

        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            $message = $this->responseMessage($decoded) ?? sprintf('Safir returned HTTP %d for %s.', $status, $endpoint);
            throw new SafirResponseException($message, $status, $rawBody, $decoded);
        }

        if ($jsonError !== null) {
            throw new SafirResponseException($jsonError, $status, $rawBody, $decoded);
        }

        return [$decoded, $response, $rawBody];
    }

    private function responseMessage(array $data): ?string
    {
        foreach (['description', 'message', 'error'] as $key) {
            if (isset($data[$key]) && is_string($data[$key]) && $data[$key] !== '') {
                return $data[$key];
            }
        }

        return null;
    }

    private function assertApiAccessKey(): void
    {
        if ($this->apiAccessKey === null || trim($this->apiAccessKey) === '') {
            throw new MissingApiAccessKeyException();
        }
    }

    private function headers(): array
    {
        return [
            'api-access-key' => $this->apiAccessKey,
            'Accept' => 'application/json',
        ];
    }

    private function url(string $endpoint): string
    {
        return $this->baseUrl.'/'.ltrim($endpoint, '/');
    }
}
