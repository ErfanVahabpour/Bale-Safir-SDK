# GitHub project information — Bale Safir SDK

This file records the repository profile after the initial page setup.

## Current repository state

- Repository: [ErfanVahabpour/Bale-Safir-SDK](https://github.com/ErfanVahabpour/Bale-Safir-SDK)
- Visibility: Public
- Default branch: `main`
- State: Public repository profile is configured. The SDK implementation, tests, CI workflow, examples, and updated documentation are in the local checkout and have not been pushed yet.
- Description, homepage, and topics: set as listed below.
- License: MIT; reflected in `composer.json` and `LICENSE`.
- Repository Issues and Projects: enabled.

## Applied About fields

**Name**

`Bale-Safir-SDK`

**Description**

`Unofficial PHP SDK for the Bale Safir messaging API, with file uploads and Laravel support. کتابخانهٔ PHP برای وب‌سرویس سفیر بله با پشتیبانی لاراول.`

**Homepage**

`https://docs.bale.ai/safir`

**Topics**

`bale`, `bale-safir`, `safir-api`, `bale-api`, `php`, `php-sdk`, `laravel`, `laravel-package`, `rest-api`, `messaging`

**License**

MIT was selected for the Safir SDK and added as `LICENSE`; the reference SDK's BSD 4-Clause license was not copied.

## Packagist profile

- Package name: `erfanvahabpour/bale-safir-sdk`
- Type: `library`
- Suggested description: `The unofficial PHP SDK for the Bale Safir messaging API`
- Homepage: `https://docs.bale.ai/safir`
- Support documentation: `https://docs.bale.ai/safir`
- Product page: `https://bale.ai/safir`
- Vendor / author metadata: mirror the existing Bale-Bot-SDK's author details if this is the same maintainer.
- Package-name availability: no package currently appears at this name on Packagist (checked 2026-11-24); recheck at publication time.

## Repository presentation

- `README.md` and `README.fa.md` now provide matching English/Persian setup guides and describe the unreleased local implementation.
- `PLAN.md` tracks implementation progress and release work.
- `Examples/` contains standalone samples for text, OTP, and media messages.
- `.github/workflows/ci.yml` runs the PHPUnit suite across PHP 8.2–8.5 and Laravel 10, 12, and 13; Laravel 11's flagged test dependencies are not allowed through Composer's security policy. Add a badge after the workflow passes on GitHub.
- Packagist version, PHP version, Laravel compatibility, license, and download badges after the first release.
- Keep the repo public and Issues enabled; use GitHub Projects only if a tracked roadmap board is useful.
