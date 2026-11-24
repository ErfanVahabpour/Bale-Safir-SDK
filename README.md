<div align="center">

# Bale Safir PHP SDK

**An unofficial PHP SDK for the Bale Safir messaging API, with planned Laravel support.**

[English](README.md) · [فارسی](README.fa.md)

</div>

---

## Project status

This repository is being set up. The SDK implementation has not started and no package has been released on Packagist yet, so there is no installable version or stable PHP API at this time.

## About Safir

Bale Safir is Bale's business messaging service. Its documented REST API sends messages to a phone number and supports message text, media, secure messages, OTPs, inline buttons, and approved templates. Files are uploaded first, then referenced by their returned `file_id`.

The Safir API uses an organization `api-access-key` header, `bot_id`, and `phone_number`. This is a separate API and credential model from the Bale Bot API; this package will not wrap bot tokens, chat IDs, polling, or webhooks.

## Planned SDK features

- Send text and media messages, including secure and OTP messages.
- Build inline URL, WebApp, and copy-text buttons.
- Send approved template messages with text fields.
- Upload files and use the returned `file_id` in a message.
- Support `request_id` for Safir's idempotency behavior.
- Provide typed responses, error details, and an optional Laravel facade/configuration.

These features are planned from the official API documentation and are not implemented yet.

## API documentation

- [Safir technical documentation](https://docs.bale.ai/safir)
- [Bale Safir product page](https://bale.ai/safir)
- [Related Bale Bot PHP SDK](https://github.com/ErfanVahabpour/Bale-Bot-SDK)

## Planned package details

- Composer package: `erfanvahabpour/bale-safir-sdk`
- PHP baseline: PHP 8.2+
- Laravel support: planned, optional

The package name and requirements are proposals until the first release.
