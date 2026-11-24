<div align="center">

# کتابخانه PHP سفیر بله

**یک SDK غیررسمی PHP برای وب‌سرویس پیام‌رسانی سفیر بله، همراه با بارگذاری فایل و اتصال اختیاری به لاراول.**

[![CI](https://github.com/ErfanVahabpour/Bale-Safir-SDK/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/ErfanVahabpour/Bale-Safir-SDK/actions/workflows/ci.yml) [![Latest Version](https://img.shields.io/packagist/v/erfanvahabpour/bale-safir-sdk.svg?style=flat-square)](https://packagist.org/packages/erfanvahabpour/bale-safir-sdk) [![License](https://img.shields.io/packagist/l/erfanvahabpour/bale-safir-sdk.svg?style=flat-square)](LICENSE)

[English](README.md) · [فارسی](README.fa.md)

</div>

---

## وضعیت پروژه

نخستین نسخهٔ عمومی `0.1.0` است. ممکن است API تا انتشار نسخهٔ `1.0.0` تغییر کند. مجوز پروژه MIT است؛ متن کامل در [LICENSE](LICENSE) آمده است.

## قابلیت‌ها

- ارسال پیام متنی، رسانه‌ای، رمزدار، OTP، قالبی و دارای دکمه شیشه‌ای.
- بارگذاری فایل و استفاده از `file_id` دریافتی در پیام.
- ارسال `request_id` برای جلوگیری از تکرار درخواست هنگام تلاش مجدد.
- پاسخ‌های تایپ‌شده و دسترسی به جزئیات خطاهای سفیر.
- استفاده مستقیم از SDK یا اتصال اختیاری آن به لاراول با Service Provider و Facade.

این بسته برای REST API پیام‌رسانی تجاری سفیر است؛ API بازوهای بله، احراز هویت با توکن بازو، دریافت به‌روزرسانی، polling، وب‌هوک و مدیریت گفتگو را پیاده‌سازی نمی‌کند.

## نیازمندی‌ها

- PHP نسخهٔ 8.2 یا جدیدتر
- Guzzle نسخهٔ 7.8 یا جدیدتر
- اتصال لاراول: نصب بودن `illuminate/support` در برنامه

## نصب

```bash
composer require erfanvahabpour/bale-safir-sdk
```

برای توسعه از روی checkout محلی، وابستگی‌ها را با `composer install` نصب کنید.

## تنظیمات

کلید دسترسی سازمان و شناسه عددی بازو را در محیط تنظیم کنید:

```dotenv
BALE_SAFIR_API_ACCESS_KEY=your-organization-access-key
BALE_SAFIR_BOT_ID=123456789
```

کلاینت مستقل به‌صورت پیش‌فرض این متغیرها را می‌خواند. همچنین می‌توانید مقادیر را مستقیم بدهید:

```php
use EFive\Bale\Safir\Api;

$api = new Api(
    apiAccessKey: 'your-organization-access-key',
    botId: 123456789,
);
```

PHP ساده فایل `.env` را خودکار نمی‌خواند. فایل محیط را با loader برنامه بارگذاری کنید یا مقادیر را مستقیم به `Api` بدهید.

## ارسال پیام متنی

```php
use EFive\Bale\Safir\Api;

$api = new Api();

$response = $api->sendMessage([
    'request_id' => bin2hex(random_bytes(16)),
    'phone_number' => '989123456789',
    'message_data' => [
        'message' => [
            'text' => 'متن پیام شما',
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

هنگام تلاش مجدد برای همان ارسال، همان `request_id` را استفاده کنید. برای پیام جدید، شناسهٔ جدید بسازید و آن را نگه‌داری کنید.

## انواع پیام

فیلدهای `message_data` مستندشدهٔ سفیر به‌صورت آرایه PHP ارسال می‌شوند:

```php
// پیام متنی رمزدار
$messageData = [
    'is_secure' => true,
    'message' => ['text' => 'پیام خصوصی'],
];

// پیام رمز یک‌بارمصرف؛ سفیر فعلاً رمز عددی می‌پذیرد
$otpData = [
    'otp_message' => ['otp' => '123456'],
];

// پیام دارای دکمهٔ پیوند
$buttonData = [
    'message' => [
        'text' => 'برای مشاهده بیشتر',
        'reply_markup' => [
            'inline_keyboard' => [[
                ['text' => 'باز کردن بله', 'url' => 'https://bale.ai'],
            ]],
        ],
    ],
];

// قالب تأییدشده
$templateData = [
    'template_message' => [
        'template_id' => 'your-template-id',
        'text_fields' => ['name' => 'علی'],
    ],
];
```

یکی از این آرایه‌ها را به‌عنوان مقدار `message_data` به `sendMessage()` بدهید. پیام رسانه‌ای از `file_id` درون `message` استفاده می‌کند و می‌تواند متن توضیحی هم داشته باشد.

دکمه‌های شیشه‌ای می‌توانند WebApp را باز کنند یا متنی را کپی کنند:

```php
['text' => 'باز کردن مینی‌اپ', 'web_app' => ['url' => 'https://example.com/app']],
['text' => 'کپی کد', 'copy_text' => 'ABC123'],
```

## بارگذاری فایل و ارسال آن

`InputFile::fromPath()` فایل محلی را به‌صورت stream به سرویس multipart سفیر می‌فرستد. مستندات API حداکثر حجم ۵۰۰ مگابایت را ذکر می‌کند.

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
            'text' => 'توضیح عکس',
            'file_id' => $upload->getFileId(),
        ],
    ],
]);
```

برای stream باز یا محتوای حافظه نیز می‌توانید از `InputFile::fromStream()` و `InputFile::fromContents()` استفاده کنید.

## لاراول

Laravel package discovery، Service Provider و Facade سفیر را ثبت می‌کند. فایل تنظیمات را منتشر کنید:

```bash
php artisan vendor:publish --tag="bale-safir-config"
```

کلاینت پیش‌فرض متغیرهای `BALE_SAFIR_API_ACCESS_KEY` و `BALE_SAFIR_BOT_ID` را می‌خواند. برای استفاده از اعتبارنامه‌های جداگانه، کلاینت‌های نام‌دار را در `config/bale-safir.php` تعریف کنید:

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
    'message_data' => ['message' => ['text' => 'سلام از لاراول']],
]);
```

## خطاها و پاسخ‌ها

- خطای HTTP یا شبکه باعث پرتاب استثنای SDK می‌شود. `SafirResponseException` کد HTTP، دادهٔ رمزگشایی‌شده و متن خام پاسخ را ارائه می‌کند.
- پاسخ HTTP موفق هم ممکن است دارای `error_data` سفیر باشد؛ `isSuccessful()` و `getErrorData()` را در `SendMessageResponse` بررسی کنید.
- `UploadFileResponse` متدهای `getFileId()`، `getError()` و `getErrorInfo()` را دارد.
- کلید دسترسی در هدر `api-access-key` فرستاده می‌شود و در URL قرار نمی‌گیرد.

## توسعه

```bash
composer install
composer test
```

## مستندات سفیر

- [مستندات فنی API](https://docs.bale.ai/safir)
- [صفحهٔ معرفی سفیر](https://bale.ai/safir)
- [SDK مرتبط برای API بازوهای بله](https://github.com/ErfanVahabpour/Bale-Bot-SDK)

مستندات سفیر در نمونه‌های شماره تلفن اختلاف دارد و فیلد خطای بارگذاری را گاهی `error` و گاهی `error_data` می‌نامد. این SDK شمارهٔ ورودی را تغییر نمی‌دهد و پاسخ خام را در دسترس می‌گذارد تا برنامه بتواند گونه‌های مختلف پاسخ را مدیریت کند.
