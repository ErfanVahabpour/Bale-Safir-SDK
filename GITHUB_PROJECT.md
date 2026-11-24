# GitHub project information — Bale Safir SDK

This file records the repository profile after the initial page setup.

## Current repository state

- Repository: [ErfanVahabpour/Bale-Safir-SDK](https://github.com/ErfanVahabpour/Bale-Safir-SDK)
- Visibility: Public
- Default branch: `main`
- State: Public repository profile is configured; the SDK implementation, tests, CI workflow, examples, and release documentation are pushed to `main`.
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
- Published package: [erfanvahabpour/bale-safir-sdk](https://packagist.org/packages/erfanvahabpour/bale-safir-sdk), current public release `v0.2.1` (MIT), published 2026-11-24. Packagist metadata points to release commit `811036a`.
- GitHub release: [v0.2.1](https://github.com/ErfanVahabpour/Bale-Safir-SDK/releases/tag/v0.2.1), published from commit `811036a` after CI passed.

## Repository presentation

- `README.md` and `README.fa.md` provide matching English/Persian API and setup guides for the public `v0.2.1` release, including method, object, upload, Laravel, error, and troubleshooting references.
- `PLAN.md` tracks implementation progress and release history.
- `Examples/` contains standalone samples for text, OTP, and media messages, with English and Persian run guides.
- `.github/workflows/ci.yml` runs the PHPUnit suite across PHP 8.2–8.5 and Laravel 12–13; its passing status is shown in both READMEs. Laravel 10–11 test dependencies are blocked by Composer's security policy.
- Packagist version and license badges are active in both READMEs.
- Keep the repo public and Issues enabled; use GitHub Projects only if a tracked roadmap board is useful.
