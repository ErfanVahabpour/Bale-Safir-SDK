# Bale Safir SDK — Implementation Plan

## Goal

Build a standalone PHP Composer SDK for the Bale Safir messaging API, modeled after the developer experience and repository structure of [Bale-Bot-SDK](https://github.com/ErfanVahabpour/Bale-Bot-SDK). Keep the two packages independent so applications can install both without class or configuration conflicts.

The Safir API is a REST service using an organization `api-access-key` header, a numeric `bot_id`, and a recipient `phone_number`. It is not the bot-token/chat-ID API implemented by Bale-Bot-SDK.

## API scope from the official docs

- `POST https://safir.bale.ai/api/v3/send_message` with JSON.
- `POST https://safir.bale.ai/api/v3/upload_file` with multipart form data; the docs state a 500 MB maximum.
- Message variants: text, media via `file_id`, secure messages, OTP, inline URL/WebApp/copy-text buttons, and template messages.
- Optional `request_id` for idempotency.
- Send responses include `message_id` and `error_data`; upload responses include `file_id` and an error field.
- Documented send error codes: 2, 3, 4, 8, 17, 20, and 21.
- The input schema documents one recipient phone number per send request. Do not imply batch sending, delivery-status lookup, bot updates, payments, webhooks, or chat administration unless Safir publishes those endpoints.

## Proposed package identity

- Composer package: `erfanvahabpour/bale-safir-sdk`
- PHP namespace: `EFive\\Bale\\Safir\\`
- Laravel facade: `EFive\\Bale\\Safir\\Laravel\\Facades\\Safir`
- Laravel service provider: `EFive\\Bale\\Safir\\Laravel\\SafirServiceProvider`
- PHP baseline: `^8.2`, aligned with the reference SDK.
- Core transport: Guzzle 7; Laravel integration remains optional.

## Build phases and progress

### 1. Lock the contract and package scaffold — complete locally

- Composer metadata, PSR-4 autoloading, and ignore rules are in place.
- The API contract, response fields, and documented error codes are recorded above.
- Safir-specific environment variable names are used, and the maintainer selected the MIT license.
- The API key is sent in the request header and is not placed in URLs or normal exception messages.

### 2. Implement the transport and core API — complete locally

- Guzzle transport supports injection, a configurable base URL, and timeouts.
- `sendMessage()` and `uploadFile()` use the documented endpoints.
- Public methods accept PHP arrays and return typed response objects.
- HTTP errors, Safir-level errors, optional response fields, and raw response data remain inspectable.
- Path-based upload uses a lazy stream; stream and in-memory inputs are also supported.

### 3. Model Safir-specific payloads and errors — complete locally

- `SendMessageResponse`, `UploadFileResponse`, and `ErrorInfo` model documented results and error codes.
- Serializable objects model standard text, inline-keyboard, and OTP payloads; template payloads remain arrays to match Safir's evolving schema.
- Upload responses expose `file_id` for use in a subsequent message.
- Low-level `sendMessage()` accepts an explicit `request_id`; the convenience send methods generate one when omitted.
- Whitespace is trimmed from phone numbers, and exact 11-digit Iranian mobile numbers beginning with `09` are normalized to `98...`.

### 4. Add Laravel support matching the reference UX — complete locally

- Composer auto-discovery registers a Laravel service provider and `Safir` facade.
- A dedicated config file supports API key, bot ID, base URL, timeouts, and named clients.
- Laravel configuration uses Safir-specific keys and does not use `BALE_BOT_TOKEN`.
- Bot-only commands, polling, events, payments, and chat APIs are out of scope.

### 5. Documentation, examples, tests, CI, and first release — complete

- English and Persian READMEs cover setup, message variants, uploads, Laravel, idempotency, errors, and release status.
- Runnable examples cover text messages, OTPs, and upload-then-send media.
- PHPUnit coverage uses mocked HTTP responses for request encoding, file uploads, Safir and HTTP errors, malformed responses, input validation, and Laravel provider/facade resolution.
- GitHub Actions covers PHP 8.2 through 8.5 and Laravel 12 and 13 using matching Testbench versions. Local tests passed on PHP 8.5.13 with Laravel 13. Laravel 10 and 11 test dependencies are currently blocked by Composer security advisories, so CI keeps those advisory checks enabled and tests the installable Laravel lines.
- Release `v0.2.0` includes phone-number normalization, convenience send methods, and serializable message payload objects.
- Packagist metadata lists `v0.2.0` with MIT and points to release commit `7a0dc4b`.
- Packagist version and license badges are active in both READMEs.

### 6. Complete documentation and publish patch release — in progress

- Expand both READMEs with the full convenience-method signatures, payload fields, upload stream lifecycle, Laravel settings, response/error details, and troubleshooting.
- Add runnable-example setup guides in English and Persian.
- Update release notes and project status for `v0.2.1`.
- Publish `v0.2.1` after the documentation commit passes repository CI, then verify the tag and Packagist metadata.

## Release status

- Current public release before phase 6: `v0.2.0` (MIT), published 2026-11-24.
- Package install command: `composer require erfanvahabpour/bale-safir-sdk`.
- Follow-up releases should incorporate user feedback and updates to Safir's API contract; the open documentation ambiguities below remain tracked.

## Suggested initial source layout

```text
src/
  Api.php
  SafirClient.php
  SafirRequest.php
  SafirResponse.php
  HttpClients/
  Methods/Message.php
  Methods/Files.php
  FileUpload/InputFile.php
  Objects/SendMessageResponse.php
  Objects/UploadFileResponse.php
  Objects/ErrorInfo.php
  Laravel/SafirServiceProvider.php
  Laravel/Facades/Safir.php
  Laravel/config/safir.php
tests/Unit/
Examples/
.github/workflows/ci.yml
README.md
README.fa.md
composer.json
phpunit.xml.dist
```

This is a starting shape, not a requirement to preserve every Bot SDK abstraction. In particular, Safir needs multipart upload and its own credentials, payloads, and response/error semantics.

## Acceptance criteria for release

- A consumer can install the package with Composer and send each documented message variant using the official Safir endpoints.
- `uploadFile()` sends multipart data with the correct API-key header and returns a usable `file_id`.
- API access keys never appear in request URLs, exceptions, or normal logs.
- HTTP failures and Safir-level `error_data` remain distinguishable and inspectable.
- The Laravel integration works with the package's declared Laravel versions without leaking bot SDK config or facade names.
- Readmes and examples describe only implemented Safir endpoints; no Bot API methods are presented as Safir features.

## Contract notes

1. `template_message` appears in examples but is omitted from the `MessageData` field table.
2. Safir requires Iranian numbers in international `98...` or `+98...` form and lists domestic `09...` numbers as invalid. The SDK converts exact 11-digit local Iranian mobile inputs to `98...`.
3. The upload section names `error`, while the general error section says services expose `error_data`.
4. OTP examples omit `error_data`, so response fields must be treated as optional.

Sources: [Safir API docs](https://docs.bale.ai/safir), [Safir product page](https://bale.ai/safir), [reference PHP SDK](https://github.com/ErfanVahabpour/Bale-Bot-SDK).
