# Changelog

## 0.2.1 — 2026-11-24

- Expand the English and Persian READMEs with complete method signatures, payload fields, upload lifecycle, Laravel configuration, errors, and troubleshooting.
- Add Bash and PowerShell example guides in English and Persian.
- Clarify request ID retry behavior, local phone-number normalization, and secret handling.

## 0.2.0 — 2026-11-24

- Add `sendOtp()`, `sendText()`, and `sendSecureText()` convenience methods.
- Add serializable `MessageData`, `Message`, `ReplyMarkup`, `InlineKeyboardButton`, and `OTPMessage` payload objects.
- Normalize local Iranian mobile numbers beginning with `09` to Safir's required `98...` format.

## 0.1.0 — 2026-11-24

- Initial public release of the Bale Safir PHP SDK.
- Add `sendMessage()` and multipart `uploadFile()` support.
- Add typed response/error objects, `InputFile`, and Laravel provider/facade support.
- Add English and Persian guides, runnable examples, PHPUnit coverage, and GitHub Actions CI.
