<div align="center">

# Bale Safir SDK

**An unofficial PHP SDK for the Bale Safir messaging API, with file uploads and optional Laravel integration.**

[![CI](https://github.com/ErfanVahabpour/Bale-Safir-SDK/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/ErfanVahabpour/Bale-Safir-SDK/actions/workflows/ci.yml) [![Latest Version](https://img.shields.io/packagist/v/erfanvahabpour/bale-safir-sdk.svg?style=flat-square)](https://packagist.org/packages/erfanvahabpour/bale-safir-sdk) [![License](https://img.shields.io/packagist/l/erfanvahabpour/bale-safir-sdk.svg?style=flat-square)](LICENSE)

[English](README.md) · [فارسی](README.fa.md)

</div>

---

## Project status

The current public release is `0.2.1`. The API may change before `1.0.0`. The project is licensed under MIT; see [LICENSE](LICENSE).

## What it supports

- Send Safir text, media, secure, OTP, template, and inline-button messages.
- Use `sendOtp()`, `sendText()`, and `sendSecureText()` convenience methods or compose the documented request arrays directly.
- Build typed, JSON-serializable payloads with `MessageData`, `Message`, `ReplyMarkup`, `InlineKeyboardButton`, and `OTPMessage`.
- Upload a file and use the returned `file_id` in a message.
- Reuse a `request_id` to make retries of the same send idempotent.
- Inspect typed responses and Safir error details.
- Use the SDK directly or through an optional Laravel service provider and facade.

This package targets Safir's business messaging REST API. It does not implement the Bale Bot API, bot-token authentication, updates, polling, webhooks, or chat administration.

## Requirements

- PHP 8.2 or later
- Guzzle 7.8 or later
- Optional Laravel integration: an application with `illuminate/support` installed. CI currently covers Laravel 12 and 13.

## Installation

```bash
composer require erfanvahabpour/bale-safir-sdk
```

For development from a local checkout, install dependencies with `composer install`.

Safir expects Iranian recipient numbers in international format (`98...` or `+98...`). An 11-digit local mobile number beginning with `09` is converted to `98...` automatically; other formats are passed through unchanged.

## Configuration

Set your organization API access key and numeric bot ID in the environment:

```dotenv
BALE_SAFIR_API_ACCESS_KEY=your-organization-access-key
BALE_SAFIR_BOT_ID=123456789
```

The standalone client reads these environment variables by default. You can also pass them explicitly:

```php
use EFive\Bale\Safir\Api;

$api = new Api(
    apiAccessKey: 'your-organization-access-key',
    botId: 123456789,
);
```

Plain PHP does not load `.env` files by itself. Load them with your application's environment loader, or pass the values directly to `Api`. Keep access keys in environment variables or a secret manager; do not commit them.

You can configure the endpoint and timeouts when constructing the client:

```php
$api = new Api(
    baseUrl: 'https://safir.bale.ai/api/v3',
    timeout: 30,
    connectTimeout: 10,
);
```

The API access key is sent in the `api-access-key` HTTP header. The SDK does not put it in the URL.

## Phone numbers and request IDs

Safir expects Iranian phone numbers in international form (`98...` or `+98...`) without separators. The SDK converts an exact 11-digit local mobile number beginning with `09` to `98...`; it trims surrounding whitespace. Other formats pass through unchanged and may be rejected by Safir.

The convenience methods generate a random request ID when you omit it. For a retry of the same logical send, pass the same `requestId` and the same message content. Generate a new request ID for a new message. The low-level `sendMessage()` method does not add a request ID for you.

## Send a text message

```php
use EFive\Bale\Safir\Api;

$api = new Api();

$response = $api->sendMessage([
    'request_id' => bin2hex(random_bytes(16)),
    'phone_number' => '989123456789',
    'message_data' => [
        'message' => [
            'text' => 'Your message text',
        ],
    ],
]);

if ($response->isSuccessful()) {
    echo $response->getMessageId();
} else {
    foreach ($response->getErrorData() as $error) {
        printf("%s: %s\n", $error->getCodeName() ?? 'Safir error', $error->getDescription() ?? '');
    }
}
```

Use the same `request_id` when retrying the same logical send. Generate and persist a new ID for a different message.

## Convenience methods and payload objects

The convenience methods return `SendMessageResponse`:

| Method | Required arguments | Optional arguments and defaults |
| --- | --- | --- |
| `sendOtp($phoneNumber, $code = null, $isSecure = false, $requestId = null)` | `phoneNumber` | `code`: random six-digit numeric string; `isSecure`: `false`; `requestId`: random ID |
| `sendText($phoneNumber, $text, $replyMarkup = null, $isSecure = false, $fileId = null, $requestId = null)` | `phoneNumber`, `text` | `replyMarkup`: `null`; `isSecure`: `false`; `fileId`: `null`; `requestId`: random ID |
| `sendSecureText($phoneNumber, $text, $replyMarkup = null, $fileId = null, $requestId = null)` | `phoneNumber`, `text` | `replyMarkup`: `null`; `fileId`: `null`; `requestId`: random ID; always sets `is_secure` to `true` |

The default OTP is generated with `random_int()` and contains six digits. Pass an explicit code and request ID when retrying an OTP send so the retry uses the same payload.

```php
use EFive\Bale\Safir\Api;
use EFive\Bale\Safir\Objects\InlineKeyboardButton;
use EFive\Bale\Safir\Objects\ReplyMarkup;

$api = new Api();
$markup = new ReplyMarkup([
    [new InlineKeyboardButton('Open Bale', url: 'https://bale.ai')],
]);

$api->sendText('09123456789', 'Hello from Safir', $markup);
$api->sendSecureText('09123456789', 'A private message');
$api->sendOtp('09123456789');
```

`sendText()` accepts a `ReplyMarkup` object or an API-shaped array. Use `fileId` to attach an already uploaded file; `text` then acts as its caption.

### Payload objects

All payload classes are in `EFive\Bale\Safir\Objects`. They provide `toArray()` and implement `JsonSerializable`; their PHP camelCase property names map to Safir's snake_case JSON fields.

| Object | Fields |
| --- | --- |
| `MessageData` | `message: ?Message`, `otpMessage: ?OTPMessage`, `isSecure: bool` |
| `Message` | `text: ?string`, `fileId: ?string`, `replyMarkup: ?ReplyMarkup\|array`, `copyText: ?string` |
| `ReplyMarkup` | `inlineKeyboard`: a list of rows; each row contains `InlineKeyboardButton` objects or button arrays. Use `ReplyMarkup::fromArray()` for an API-shaped array. |
| `InlineKeyboardButton` | `text: string`, and optionally `url: ?string`, `webApp: ?array` (for example `['url' => 'https://example.com/app']`), `copyText: ?string`. Safir requires at least one button action. |
| `OTPMessage` | `otp: string\|int`; digits only. String codes preserve leading zeros. |

You can also pass a `MessageData` object to low-level `sendMessage()`:

```php
use EFive\Bale\Safir\Objects\Message;
use EFive\Bale\Safir\Objects\MessageData;

$response = $api->sendMessage([
    'phone_number' => '989123456789',
    'request_id' => 'stable-id-for-this-send',
    'message_data' => new MessageData(
        message: new Message(text: 'Hello from a payload object'),
    ),
]);
```

## Message variants

The low-level `sendMessage()` method accepts Safir's evolving payload fields as PHP arrays. Convenience payload objects cover standard text, inline-keyboard, and OTP messages. Use an array for template payloads, which do not currently have a dedicated object class.

```php
// A secure text message
$messageData = [
    'is_secure' => true,
    'message' => ['text' => 'Private message'],
];

// An OTP message; Safir currently supports numeric OTP values
$otpData = [
    'otp_message' => ['otp' => '123456'],
];

// A message with an inline URL button
$buttonData = [
    'message' => [
        'text' => 'Read more',
        'reply_markup' => [
            'inline_keyboard' => [[
                ['text' => 'Open Bale', 'url' => 'https://bale.ai'],
            ]],
        ],
    ],
];

// A previously approved template
$templateData = [
    'template_message' => [
        'template_id' => 'your-template-id',
        'text_fields' => ['name' => 'Ali'],
    ],
];
```

Pass one of these arrays as the `message_data` value to `sendMessage()`. A media message uses a `file_id` inside `message`, optionally with a text caption.

Inline buttons can also open a WebApp or copy text. At least one of `url`, `web_app`, or `copy_text` must be set for each button:

```php
['text' => 'Open mini app', 'web_app' => ['url' => 'https://example.com/app']],
['text' => 'Copy code', 'copy_text' => 'ABC123'],
```

## Upload a file and send it

`uploadFile()` sends a multipart request to Safir's upload endpoint and returns `UploadFileResponse`. Safir's documentation states a 500 MB maximum. `InputFile::fromPath()` streams a local file; `fromStream()` accepts an open PHP stream resource, and `fromContents()` accepts an in-memory string.

```php
use EFive\Bale\Safir\Api;
use EFive\Bale\Safir\FileUpload\InputFile;

$api = new Api();
$upload = $api->uploadFile(InputFile::fromPath(__DIR__.'/photo.jpg'));

if (! $upload->isSuccessful()) {
    throw new RuntimeException('Safir did not return a file_id.');
}

$sent = $api->sendMessage([
    'phone_number' => '989123456789',
    'message_data' => [
        'message' => [
            'text' => 'Photo caption',
            'file_id' => $upload->getFileId(),
        ],
    ],
]);
```

You can also build an input file from an open stream or in-memory contents with `InputFile::fromStream()` and `InputFile::fromContents()`.

For `InputFile::fromStream()`, keep the stream open through the request and close it when your code is finished with it. `InputFile::fromPath()` opens the file lazily for the upload.

## Laravel

Laravel package discovery registers the service provider and `Safir` facade. Publish the configuration file:

```bash
php artisan vendor:publish --tag="bale-safir-config"
```

The default client reads `BALE_SAFIR_API_ACCESS_KEY` and `BALE_SAFIR_BOT_ID`. Add named clients to `config/bale-safir.php` when you need separate Safir credentials:

```php
'default' => 'default',
'clients' => [
    'default' => [
        'api_access_key' => env('BALE_SAFIR_API_ACCESS_KEY'),
        'bot_id' => env('BALE_SAFIR_BOT_ID'),
    ],
    'marketing' => [
        'api_access_key' => env('BALE_SAFIR_MARKETING_API_ACCESS_KEY'),
        'bot_id' => env('BALE_SAFIR_MARKETING_BOT_ID'),
    ],
],
```

```php
use EFive\Bale\Safir\Laravel\Facades\Safir;

$response = Safir::client('marketing')->sendText('989123456789', 'Hello from Laravel');
```

Configuration also supports `base_url`, `timeout` (default 30 seconds), and `connect_timeout` (default 10 seconds). `Safir::client()` returns an `Api` instance; the facade forwards its send and upload methods to the default client as well.

## Errors and responses

- Non-2xx responses and invalid JSON throw `SafirResponseException`. It exposes `getHttpStatusCode()`, `getResponseData()`, and `getRawResponse()`.
- Transport failures throw `SafirNetworkException`. The exception message does not include the API key or raw request data.
- HTTP 2xx does not by itself mean the message was accepted. Check `SendMessageResponse::isSuccessful()`, `hasErrors()`, `getMessageId()`, and `getErrorData()`.
- Each `ErrorInfo` provides `getPhoneNumber()`, `getCode()`, `getCodeName()`, and `getDescription()`.
- `UploadFileResponse` provides `isSuccessful()`, `getFileId()`, `getError()`, and `getErrorInfo()`; the SDK accepts both documented upload error keys, `error` and `error_data`.

Safir documents these send error codes:

| Code | Name | Meaning |
| --- | --- | --- |
| 2 | `InternalServerError` | Internal service error |
| 3 | `RateLimitExceeded` | Sending limit exceeded |
| 4 | `InvalidInput` | Invalid JSON input |
| 8 | `InvalidPhone` | Invalid recipient phone number |
| 17 | `NotBaleUser` | Recipient has no Bale account |
| 20 | `PaymentRequired` | Insufficient credit |
| 21 | `MaximumContactLimitReached` | Bot contact limit reached |

Error descriptions and HTTP status behavior may vary by endpoint. Refer to Safir's current documentation for the authoritative contract.

## Development

```bash
composer install
composer test
```

The runnable examples read credentials and inputs from environment variables. After setting `BALE_SAFIR_API_ACCESS_KEY`, `BALE_SAFIR_BOT_ID`, and `BALE_SAFIR_PHONE_NUMBER`, run:

```bash
php Examples/sendMessage.php
BALE_SAFIR_OTP=123456 php Examples/sendOtp.php
BALE_SAFIR_FILE=/path/to/photo.jpg php Examples/uploadAndSendMedia.php
```

`BALE_SAFIR_OTP` is read only by the OTP example; the media example also requires `BALE_SAFIR_FILE` to point to a readable local file. See the example guides in [English](Examples/README.md) and [Persian](Examples/README.fa.md) for shell notes.

## Troubleshooting

- `MissingApiAccessKeyException`: set `BALE_SAFIR_API_ACCESS_KEY` or pass `apiAccessKey` to `Api`.
- `MissingBotIdException`: set `BALE_SAFIR_BOT_ID` or pass `botId` to `Api`.
- `InvalidPhone` (`8`): send `98...` or `+98...` without separators. Standard local mobile numbers beginning with `09` are normalized automatically.
- A response with `error_data` may have HTTP status 2xx; inspect the typed response rather than treating the HTTP status alone as success.
- Template messages must already be created and approved in the Bale business panel, and `text_fields` names must match the approved template.

## Safir documentation

- [API documentation](https://docs.bale.ai/safir)
- [Safir product page](https://bale.ai/safir)
- [Related Bale Bot SDK](https://github.com/ErfanVahabpour/Bale-Bot-SDK)

Safir's documentation describes upload errors under both `error` and `error_data`; the SDK accepts both response variants.
