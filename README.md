<div align="center">

# Bale Safir SDK

**An unofficial PHP SDK for the Bale Safir messaging API, with file uploads and optional Laravel integration.**

[![CI](https://github.com/ErfanVahabpour/Bale-Safir-SDK/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/ErfanVahabpour/Bale-Safir-SDK/actions/workflows/ci.yml) [![Latest Version](https://img.shields.io/packagist/v/erfanvahabpour/bale-safir-sdk.svg?style=flat-square)](https://packagist.org/packages/erfanvahabpour/bale-safir-sdk) [![License](https://img.shields.io/packagist/l/erfanvahabpour/bale-safir-sdk.svg?style=flat-square)](LICENSE)

[English](README.md) · [فارسی](README.fa.md)

</div>

---

## Project status

The current public release is `0.2.0`. The API may change before `1.0.0`. The project is licensed under MIT; see [LICENSE](LICENSE).

## What it supports

- Send Safir text, media, secure, OTP, template, and inline-button messages.
- Upload a file and use the returned `file_id` in a message.
- Pass a `request_id` to make retries idempotent.
- Inspect typed responses and Safir error details.
- Use the SDK directly or through an optional Laravel service provider and facade.

This package targets Safir's business messaging REST API. It does not implement the Bale Bot API, bot-token authentication, updates, polling, webhooks, or chat administration.

## Requirements

- PHP 8.2 or later
- Guzzle 7.8 or later
- Laravel integration: an application with `illuminate/support` installed

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

Plain PHP does not load `.env` files by itself. Load them with your application's environment loader, or pass the values directly to `Api`.

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

The SDK also provides `sendOtp()`, `sendText()`, and `sendSecureText()`. They generate a random request ID when one is omitted. `sendOtp()` generates a random six-digit numeric code by default; pass the code and request ID explicitly when you need stable values for retries.

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

`MessageData`, `Message`, `ReplyMarkup`, `InlineKeyboardButton`, and `OTPMessage` are serializable payload objects in `EFive\Bale\Safir\Objects`. You can pass a `MessageData` instance as `message_data` to `sendMessage()` or use the objects with the convenience methods.

## Message variants

The SDK passes Safir's documented `message_data` fields as PHP arrays:

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

Inline buttons can also open a WebApp or copy text:

```php
['text' => 'Open mini app', 'web_app' => ['url' => 'https://example.com/app']],
['text' => 'Copy code', 'copy_text' => 'ABC123'],
```

## Upload a file and send it

`InputFile::fromPath()` streams a local file to Safir's multipart upload endpoint. The API documentation states a 500 MB maximum.

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

$response = Safir::client('marketing')->sendMessage([
    'phone_number' => '989123456789',
    'message_data' => ['message' => ['text' => 'Hello from Laravel']],
]);
```

## Errors and responses

- HTTP/network failures throw SDK exceptions. `SafirResponseException` exposes the HTTP status, decoded response data, and raw body.
- A successful HTTP response may still contain Safir `error_data`; inspect `isSuccessful()` and `getErrorData()` on `SendMessageResponse`.
- `UploadFileResponse` exposes `getFileId()`, `getError()`, and `getErrorInfo()`.
- Access keys are sent in the `api-access-key` header, never in the URL.

## Development

```bash
composer install
composer test
```

## Safir documentation

- [API documentation](https://docs.bale.ai/safir)
- [Safir product page](https://bale.ai/safir)
- [Related Bale Bot SDK](https://github.com/ErfanVahabpour/Bale-Bot-SDK)

Safir's documentation describes upload errors under both `error` and `error_data`; the SDK accepts both response variants.
