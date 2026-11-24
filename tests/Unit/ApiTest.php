<?php

namespace EFive\Bale\Safir\Tests\Unit;

use EFive\Bale\Safir\Api;
use EFive\Bale\Safir\Exceptions\InvalidRequestException;
use EFive\Bale\Safir\Exceptions\MissingApiAccessKeyException;
use EFive\Bale\Safir\Exceptions\MissingBotIdException;
use EFive\Bale\Safir\Exceptions\SafirNetworkException;
use EFive\Bale\Safir\Exceptions\SafirResponseException;
use EFive\Bale\Safir\FileUpload\InputFile;
use EFive\Bale\Safir\Objects\InlineKeyboardButton;
use EFive\Bale\Safir\Objects\ReplyMarkup;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class ApiTest extends TestCase
{
    /** @var array<string, array{process:string|false, env:array{exists:bool, value:mixed}, server:array{exists:bool, value:mixed}}> */
    private array $savedEnvironment = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([Api::API_ACCESS_KEY_ENV_NAME, Api::BOT_ID_ENV_NAME] as $name) {
            $this->savedEnvironment[$name] = [
                'process' => getenv($name),
                'env' => ['exists' => array_key_exists($name, $_ENV), 'value' => $_ENV[$name] ?? null],
                'server' => ['exists' => array_key_exists($name, $_SERVER), 'value' => $_SERVER[$name] ?? null],
            ];

            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->savedEnvironment as $name => $values) {
            if ($values['process'] === false) {
                putenv($name);
            } else {
                putenv($name.'='.$values['process']);
            }

            if ($values['env']['exists']) {
                $_ENV[$name] = $values['env']['value'];
            } else {
                unset($_ENV[$name]);
            }

            if ($values['server']['exists']) {
                $_SERVER[$name] = $values['server']['value'];
            } else {
                unset($_SERVER[$name]);
            }
        }

        parent::tearDown();
    }

    public function testSendMessageUsesSafirEndpointAndReturnsTypedResponse(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], '{"message_id":"message-123","error_data":null}'),
        ], $history);

        $response = $api->sendMessage([
            'request_id' => 'request-123',
            'phone_number' => '989123456789',
            'message_data' => ['message' => ['text' => 'Hello']],
        ]);

        self::assertTrue($response->isSuccessful());
        self::assertSame('message-123', $response->getMessageId());
        self::assertSame(200, $response->getHttpStatusCode());
        self::assertSame('{"message_id":"message-123","error_data":null}', $response->getRawResponse());

        $request = $history[0]['request'];
        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://safir.test/api/v3/send_message', (string) $request->getUri());
        self::assertSame('test-api-key', $request->getHeaderLine('api-access-key'));
        self::assertSame([
            'request_id' => 'request-123',
            'phone_number' => '989123456789',
            'message_data' => ['message' => ['text' => 'Hello']],
            'bot_id' => 123456789,
        ], json_decode((string) $request->getBody(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testLocalIranianMobileNumberIsNormalizedForSafir(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], '{"message_id":"message-123","error_data":null}'),
        ], $history);

        $response = $api->sendMessage([
            'phone_number' => '09123456789',
            'message_data' => ['message' => ['text' => 'Hello']],
        ]);

        self::assertTrue($response->isSuccessful());
        self::assertSame(
            '989123456789',
            json_decode((string) $history[0]['request']->getBody(), true, 512, JSON_THROW_ON_ERROR)['phone_number']
        );
    }

    public function testSendOtpGeneratesSixDigitCodeAndRequestIdByDefault(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], '{"message_id":"otp-message-123"}'),
        ], $history);

        $response = $api->sendOtp('09123456789');
        $payload = json_decode((string) $history[0]['request']->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($response->isSuccessful());
        self::assertSame('989123456789', $payload['phone_number']);
        self::assertMatchesRegularExpression('/\\A\\d{6}\\z/', $payload['message_data']['otp_message']['otp']);
        self::assertMatchesRegularExpression('/\\A[a-f0-9]{32}\\z/', $payload['request_id']);
        self::assertFalse($payload['message_data']['is_secure']);
    }

    public function testSendOtpUsesProvidedCodeSecurityAndRequestId(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], '{"message_id":"otp-message-123"}'),
        ], $history);

        $response = $api->sendOtp('989123456789', '001234', true, 'otp-request-123');
        $payload = json_decode((string) $history[0]['request']->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($response->isSuccessful());
        self::assertSame('001234', $payload['message_data']['otp_message']['otp']);
        self::assertTrue($payload['message_data']['is_secure']);
        self::assertSame('otp-request-123', $payload['request_id']);
    }

    public function testSendTextSerializesObjectsAndGeneratesDefaults(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], '{"message_id":"message-123","error_data":null}'),
        ], $history);
        $markup = new ReplyMarkup([
            [new InlineKeyboardButton('Open Bale', url: 'https://bale.ai')],
        ]);

        $response = $api->sendText('09123456789', 'Hello', $markup, fileId: 'file-123');
        $payload = json_decode((string) $history[0]['request']->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($response->isSuccessful());
        self::assertSame('989123456789', $payload['phone_number']);
        self::assertSame('Hello', $payload['message_data']['message']['text']);
        self::assertSame('file-123', $payload['message_data']['message']['file_id']);
        self::assertSame([
            'inline_keyboard' => [[['text' => 'Open Bale', 'url' => 'https://bale.ai']]],
        ], $payload['message_data']['message']['reply_markup']);
        self::assertFalse($payload['message_data']['is_secure']);
        self::assertMatchesRegularExpression('/\\A[a-f0-9]{32}\\z/', $payload['request_id']);
    }

    public function testSendSecureTextSetsSecurityAndAcceptsApiMarkupArray(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], '{"message_id":"message-123","error_data":null}'),
        ], $history);
        $markup = [
            'inline_keyboard' => [[['text' => 'Open Bale', 'url' => 'https://bale.ai']]],
        ];

        $response = $api->sendSecureText('989123456789', 'Private', $markup, 'file-456', 'secure-request-123');
        $payload = json_decode((string) $history[0]['request']->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertTrue($response->isSuccessful());
        self::assertSame('file-456', $payload['message_data']['message']['file_id']);
        self::assertSame($markup, $payload['message_data']['message']['reply_markup']);
        self::assertTrue($payload['message_data']['is_secure']);
        self::assertSame('secure-request-123', $payload['request_id']);
    }

    public function testSafirLevelErrorsAreAvailableOnSuccessfulHttpResponse(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], '{"message_id":null,"error_data":[{"phone_number":"989123456789","code":8,"description":"Invalid phone"}]}'),
        ], $history);

        $response = $api->sendMessage($this->messagePayload());
        $errors = $response->getErrorData();

        self::assertFalse($response->isSuccessful());
        self::assertTrue($response->hasErrors());
        self::assertCount(1, $errors);
        self::assertSame(8, $errors[0]->getCode());
        self::assertSame('InvalidPhone', $errors[0]->getCodeName());
        self::assertSame('Invalid phone', $errors[0]->getDescription());
    }

    public function testOtpResponseWithoutErrorDataIsSuccessful(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], '{"message_id":"otp-message-123"}'),
        ], $history);

        $response = $api->sendMessage([
            'phone_number' => '989123456789',
            'message_data' => ['otp_message' => ['otp' => '123456']],
        ]);

        self::assertTrue($response->isSuccessful());
        self::assertSame('otp-message-123', $response->getMessageId());
        self::assertSame([], $response->getErrorData());
    }

    public function testHttpFailurePreservesStatusAndRawBody(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(401, [], '{"description":"Unauthorized"}'),
        ], $history);

        try {
            $api->sendMessage($this->messagePayload());
            self::fail('Expected a SafirResponseException.');
        } catch (SafirResponseException $exception) {
            self::assertSame(401, $exception->getHttpStatusCode());
            self::assertSame('{"description":"Unauthorized"}', $exception->getRawResponse());
            self::assertSame('Unauthorized', $exception->getMessage());
        }
    }

    public function testMalformedJsonResponseThrowsResponseException(): void
    {
        $history = [];
        $api = $this->makeApi([new Response(200, [], '{invalid')], $history);

        $this->expectException(SafirResponseException::class);
        $api->sendMessage($this->messagePayload());
    }

    public function testJsonArrayResponseIsRejectedAsWrongShape(): void
    {
        $history = [];
        $api = $this->makeApi([new Response(200, [], '[]')], $history);

        $this->expectException(SafirResponseException::class);
        $this->expectExceptionMessage('JSON value instead of an object');
        $api->sendMessage($this->messagePayload());
    }

    public function testMissingApiKeyFailsBeforeRequest(): void
    {
        $history = [];
        $api = $this->makeApi([new Response(200, [], '{}')], $history, apiAccessKey: '');

        $this->expectException(MissingApiAccessKeyException::class);
        $api->sendMessage($this->messagePayload());
    }

    public function testMissingBotIdFailsBeforeRequest(): void
    {
        $history = [];
        $api = $this->makeApi([new Response(200, [], '{}')], $history, botId: null);

        $this->expectException(MissingBotIdException::class);
        $api->sendMessage($this->messagePayload());
    }

    public function testInvalidMessagePayloadIsRejectedBeforeRequest(): void
    {
        $history = [];
        $api = $this->makeApi([new Response(200, [], '{}')], $history);

        $this->expectException(InvalidRequestException::class);
        $api->sendMessage(['phone_number' => '989123456789', 'message_data' => 'invalid']);
    }

    public function testInvalidBotIdIsRejectedAsRequestError(): void
    {
        $history = [];
        $api = $this->makeApi([new Response(200, [], '{}')], $history);
        $payload = $this->messagePayload();
        $payload['bot_id'] = 12.5;

        $this->expectException(InvalidRequestException::class);
        $api->sendMessage($payload);
    }

    public function testUploadFileUsesMultipartAndReturnsFileId(): void
    {
        $history = [];
        $api = $this->makeApi([
            new Response(200, [], '{"file_id":"file-123"}'),
        ], $history);

        $response = $api->uploadFile(InputFile::fromContents('sample file contents', 'sample.txt', 'text/plain'));
        $request = $history[0]['request'];
        $body = (string) $request->getBody();

        self::assertTrue($response->isSuccessful());
        self::assertSame('file-123', $response->getFileId());
        self::assertSame('POST', $request->getMethod());
        self::assertSame('https://safir.test/api/v3/upload_file', (string) $request->getUri());
        self::assertSame('test-api-key', $request->getHeaderLine('api-access-key'));
        self::assertStringStartsWith('multipart/form-data; boundary=', $request->getHeaderLine('Content-Type'));
        self::assertStringContainsString('name="file"; filename="sample.txt"', $body);
        self::assertStringContainsString('Content-Type: text/plain', $body);
        self::assertStringContainsString('sample file contents', $body);
    }

    public function testUploadErrorFieldsAreAvailableAcrossDocumentedVariants(): void
    {
        foreach ([
            '{"error":{"phone_number":"989123456789","code":4,"description":"Invalid input"}}',
            '{"error_data":[{"phone_number":"989123456789","code":4,"description":"Invalid input"}]}',
        ] as $body) {
            $history = [];
            $api = $this->makeApi([new Response(200, [], $body)], $history);
            $response = $api->uploadFile(InputFile::fromContents('x', 'x.txt'));

            self::assertFalse($response->isSuccessful());
            self::assertSame(4, $response->getErrorInfo()?->getCode());
            self::assertSame('Invalid input', $response->getErrorInfo()?->getDescription());
        }
    }

    public function testNetworkErrorDoesNotExposeApiKey(): void
    {
        $history = [];
        $request = new Request('POST', 'https://safir.test/api/v3/send_message');
        $api = $this->makeApi([
            new ConnectException('connection failed', $request),
        ], $history);

        try {
            $api->sendMessage($this->messagePayload());
            self::fail('Expected a SafirNetworkException.');
        } catch (SafirNetworkException $exception) {
            self::assertStringNotContainsString('test-api-key', $exception->getMessage());
            self::assertSame('The request to the Safir API failed because of a network error.', $exception->getMessage());
        }
    }

    /** @return array{phone_number:string, message_data:array} */
    private function messagePayload(): array
    {
        return [
            'phone_number' => '989123456789',
            'message_data' => ['message' => ['text' => 'Hello']],
        ];
    }

    /**
     * @param list<\Throwable|Response> $responses
     * @param list<array<string, mixed>> $history
     */
    private function makeApi(
        array $responses,
        array &$history,
        ?string $apiAccessKey = 'test-api-key',
        int|string|null $botId = 123456789
    ): Api {
        $history = [];
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($history));
        $httpClient = new Client(['handler' => $stack]);

        return new Api(
            apiAccessKey: $apiAccessKey,
            botId: $botId,
            httpClient: $httpClient,
            baseUrl: 'https://safir.test/api/v3'
        );
    }
}
