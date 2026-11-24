<div align="center">

# کتابخانه PHP سفیر بله

**یک SDK غیررسمی PHP برای وب‌سرویس پیام‌رسانی سفیر بله، همراه با بارگذاری فایل و اتصال اختیاری به لاراول.**

[![CI](https://github.com/ErfanVahabpour/Bale-Safir-SDK/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/ErfanVahabpour/Bale-Safir-SDK/actions/workflows/ci.yml) [![Latest Version](https://img.shields.io/packagist/v/erfanvahabpour/bale-safir-sdk.svg?style=flat-square)](https://packagist.org/packages/erfanvahabpour/bale-safir-sdk) [![License](https://img.shields.io/packagist/l/erfanvahabpour/bale-safir-sdk.svg?style=flat-square)](LICENSE)

[English](README.md) · [فارسی](README.fa.md)

</div>

---

## وضعیت پروژه

نسخهٔ عمومی جاری `0.2.1` است. ممکن است API تا انتشار نسخهٔ `1.0.0` تغییر کند. مجوز پروژه MIT است؛ متن کامل در [LICENSE](LICENSE) آمده است.

## قابلیت‌ها

- ارسال پیام متنی، رسانه‌ای، رمزدار، OTP، قالبی و دارای دکمه شیشه‌ای.
- استفاده از متدهای سادهٔ `sendOtp()`، `sendText()` و `sendSecureText()` یا ساخت مستقیم payload آرایه‌ای.
- ساخت payloadهای قابل‌سریال‌سازی با کلاس‌های `MessageData`، `Message`، `ReplyMarkup`، `InlineKeyboardButton` و `OTPMessage`.
- بارگذاری فایل و استفاده از `file_id` دریافتی در پیام.
- استفادهٔ دوباره از `request_id` برای جلوگیری از ارسال تکراری هنگام تلاش مجدد برای همان پیام.
- پاسخ‌های تایپ‌شده و دسترسی به جزئیات خطاهای سفیر.
- استفاده مستقیم از SDK یا اتصال اختیاری آن به لاراول با Service Provider و Facade.

این بسته برای REST API پیام‌رسانی تجاری سفیر است؛ API بازوهای بله، احراز هویت با توکن بازو، دریافت به‌روزرسانی، polling، وب‌هوک و مدیریت گفتگو را پیاده‌سازی نمی‌کند.

## نیازمندی‌ها

- PHP نسخهٔ 8.2 یا جدیدتر
- Guzzle نسخهٔ 7.8 یا جدیدتر
- اتصال اختیاری لاراول: نصب بودن `illuminate/support` در برنامه. CI نسخه‌های لاراول 12 و 13 را پوشش می‌دهد.

## نصب

```bash
composer require erfanvahabpour/bale-safir-sdk
```

برای توسعه از روی checkout محلی، وابستگی‌ها را با `composer install` نصب کنید.

سفیر شمارهٔ مقصد ایران را با قالب بین‌المللی (`98...` یا `+98...`) می‌خواهد. شمارهٔ موبایل محلی ۱۱ رقمی که با `09` شروع شود، خودکار به قالب `98...` تبدیل می‌شود؛ قالب‌های دیگر بدون تغییر ارسال می‌شوند.

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

PHP ساده فایل `.env` را خودکار نمی‌خواند. فایل محیط را با loader برنامه بارگذاری کنید یا مقادیر را مستقیم به `Api` بدهید. کلید را در متغیر محیطی یا secret manager نگه دارید و آن را commit نکنید.

آدرس API و timeoutها را هم می‌توانید هنگام ساخت client تنظیم کنید:

```php
$api = new Api(
    baseUrl: 'https://safir.bale.ai/api/v3',
    timeout: 30,
    connectTimeout: 10,
);
```

کلید دسترسی در هدر HTTP با نام `api-access-key` ارسال می‌شود و در URL قرار نمی‌گیرد.

## شماره تلفن و شناسهٔ درخواست

سفیر شمارهٔ ایران را بدون جداکننده و با قالب بین‌المللی `98...` یا `+98...` می‌خواهد. SDK شمارهٔ موبایل محلیِ دقیقاً ۱۱ رقمی را که با `09` شروع شود به `98...` تبدیل می‌کند و فاصلهٔ ابتدا و انتهای شماره را حذف می‌کند. قالب‌های دیگر بدون تغییر می‌مانند و ممکن است از طرف سفیر رد شوند.

متدهای ساده در صورت ندادن `requestId`، شناسهٔ تصادفی می‌سازند. برای تلاش مجدد همان پیام، همان `requestId` و همان محتوای پیام را دوباره بفرستید. برای پیام جدید شناسهٔ تازه بسازید. متد سطح پایین `sendMessage()` خودش شناسه تولید نمی‌کند.

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

## متدهای ساده و آبجکت‌های پیام

متدهای ساده پاسخ `SendMessageResponse` می‌دهند:

| متد | ورودی‌های اجباری | ورودی‌های اختیاری و مقدار پیش‌فرض |
| --- | --- | --- |
| `sendOtp($phoneNumber, $code = null, $isSecure = false, $requestId = null)` | `phoneNumber` | `code`: رمز عددی تصادفی شش‌رقمی؛ `isSecure`: `false`؛ `requestId`: شناسهٔ تصادفی |
| `sendText($phoneNumber, $text, $replyMarkup = null, $isSecure = false, $fileId = null, $requestId = null)` | `phoneNumber`، `text` | `replyMarkup`: `null`؛ `isSecure`: `false`؛ `fileId`: `null`؛ `requestId`: شناسهٔ تصادفی |
| `sendSecureText($phoneNumber, $text, $replyMarkup = null, $fileId = null, $requestId = null)` | `phoneNumber`، `text` | `replyMarkup`: `null`؛ `fileId`: `null`؛ `requestId`: شناسهٔ تصادفی؛ همیشه `is_secure` را `true` می‌کند |

رمز پیش‌فرض `sendOtp()` با `random_int()` ساخته می‌شود. برای تلاش مجدد با payload ثابت، رمز و شناسهٔ درخواست را خودتان بدهید.

```php
use EFive\Bale\Safir\Api;
use EFive\Bale\Safir\Objects\InlineKeyboardButton;
use EFive\Bale\Safir\Objects\ReplyMarkup;

$api = new Api();
$markup = new ReplyMarkup([
    [new InlineKeyboardButton('باز کردن بله', url: 'https://bale.ai')],
]);

$api->sendText('09123456789', 'پیام آزمایشی', $markup);
$api->sendSecureText('09123456789', 'پیام محرمانه');
$api->sendOtp('09123456789');
```

`sendText()` یک آبجکت `ReplyMarkup` یا آرایه با ساختار API می‌پذیرد. با `fileId` یک فایل بارگذاری‌شده را پیوست کنید؛ در این حالت `text` توضیح فایل است.

### آبجکت‌های payload

همهٔ کلاس‌ها در فضای نام `EFive\Bale\Safir\Objects` هستند. هر کلاس `toArray()` دارد و `JsonSerializable` را پیاده‌سازی می‌کند. نام ویژگی‌های camelCase در JSON به snake_case تبدیل می‌شود.

| آبجکت | فیلدها |
| --- | --- |
| `MessageData` | `message: ?Message`، `otpMessage: ?OTPMessage`، `isSecure: bool` |
| `Message` | `text: ?string`، `fileId: ?string`، `replyMarkup: ?ReplyMarkup\|array`، `copyText: ?string` |
| `ReplyMarkup` | `inlineKeyboard`: فهرست ردیف‌ها؛ هر ردیف شامل آبجکت `InlineKeyboardButton` یا آرایه است. برای آرایهٔ کامل API از `ReplyMarkup::fromArray()` استفاده کنید. |
| `InlineKeyboardButton` | `text: string` و فیلدهای اختیاری `url: ?string`، `webApp: ?array` (مانند `['url' => 'https://example.com/app']`)، `copyText: ?string`. هر دکمه باید دست‌کم یک عملکرد داشته باشد. |
| `OTPMessage` | `otp: string\|int`؛ فقط رقم می‌پذیرد. رشتهٔ کد صفرهای ابتدای آن را نگه می‌دارد. |

می‌توانید `MessageData` را مستقیماً به `sendMessage()` بدهید:

```php
use EFive\Bale\Safir\Objects\Message;
use EFive\Bale\Safir\Objects\MessageData;

$response = $api->sendMessage([
    'phone_number' => '989123456789',
    'request_id' => 'stable-id-for-this-send',
    'message_data' => new MessageData(
        message: new Message(text: 'سلام از یک payload object'),
    ),
]);
```

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

یکی از این آرایه‌ها را به‌عنوان مقدار `message_data` به `sendMessage()` بدهید. پیام رسانه‌ای از `file_id` درون `message` استفاده می‌کند و می‌تواند متن توضیحی هم داشته باشد. آبجکت‌های payload برای متن، OTP و دکمه‌های شیشه‌ای هستند؛ برای `template_message` از آرایه استفاده کنید.

هر دکمه باید دست‌کم یکی از `url`، `web_app` یا `copy_text` را داشته باشد. دکمه‌های شیشه‌ای می‌توانند WebApp را باز کنند یا متنی را کپی کنند:

```php
['text' => 'باز کردن مینی‌اپ', 'web_app' => ['url' => 'https://example.com/app']],
['text' => 'کپی کد', 'copy_text' => 'ABC123'],
```

## بارگذاری فایل و ارسال آن

`uploadFile()` درخواست multipart می‌فرستد و `UploadFileResponse` برمی‌گرداند. مستندات API حداکثر حجم ۵۰۰ مگابایت را ذکر می‌کند. `InputFile::fromPath()` فایل محلی را stream می‌کند؛ `fromStream()` یک منبع stream باز و `fromContents()` یک رشتهٔ درون حافظه می‌گیرد.

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

برای `InputFile::fromStream()`، stream را تا پایان درخواست باز نگه دارید و بعد از اتمام کار خودتان ببندید. `InputFile::fromPath()` فایل را هنگام بارگذاری باز می‌کند.

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

تنظیمات `base_url`، `timeout` (پیش‌فرض ۳۰ ثانیه) و `connect_timeout` (پیش‌فرض ۱۰ ثانیه) نیز پشتیبانی می‌شوند. `Safir::client()` یک نمونه از `Api` برمی‌گرداند؛ Facade متدهای ارسال و بارگذاری کلاینت پیش‌فرض را هم مستقیم در دسترس می‌گذارد.

## خطاها و پاسخ‌ها

- پاسخ HTTP ناموفق یا JSON نامعتبر باعث `SafirResponseException` می‌شود. این استثنا `getHttpStatusCode()`، `getResponseData()` و `getRawResponse()` دارد.
- خطای ارتباط شبکه `SafirNetworkException` می‌دهد. پیام این استثنا کلید دسترسی یا بدنهٔ درخواست را شامل نمی‌شود.
- پاسخ HTTP با وضعیت ۲xx لزوماً به معنی پذیرفته‌شدن پیام نیست. `SendMessageResponse::isSuccessful()`، `hasErrors()`، `getMessageId()` و `getErrorData()` را بررسی کنید.
- هر `ErrorInfo` متدهای `getPhoneNumber()`، `getCode()`، `getCodeName()` و `getDescription()` را دارد.
- `UploadFileResponse` متدهای `isSuccessful()`، `getFileId()`، `getError()` و `getErrorInfo()` را دارد و هر دو فیلد `error` و `error_data` را می‌پذیرد.

کدهای خطای ارسال مستندشده:

| کد | نام | معنی |
| --- | --- | --- |
| 2 | `InternalServerError` | خطای داخلی سرویس |
| 3 | `RateLimitExceeded` | محدودیت تعداد ارسال |
| 4 | `InvalidInput` | ورودی JSON نامعتبر |
| 8 | `InvalidPhone` | شمارهٔ مقصد نامعتبر |
| 17 | `NotBaleUser` | گیرنده حساب بله ندارد |
| 20 | `PaymentRequired` | اعتبار کافی نیست |
| 21 | `MaximumContactLimitReached` | سقف مخاطبان بازو پر شده است |

شرح خطا و رفتار HTTP ممکن است میان endpointها فرق کند؛ برای قرارداد نهایی به مستندات فعلی سفیر مراجعه کنید.

## توسعه

```bash
composer install
composer test
```

نمونه‌های اجرایی درخواست واقعی به سفیر می‌فرستند؛ آن‌ها را فقط با بازو و شماره‌ای که مجاز به استفاده از آن هستید اجرا کنید. نمونه‌ها متغیرهای `BALE_SAFIR_API_ACCESS_KEY`، `BALE_SAFIR_BOT_ID` و `BALE_SAFIR_PHONE_NUMBER` را می‌خوانند:

```bash
php Examples/sendMessage.php
BALE_SAFIR_OTP=123456 php Examples/sendOtp.php
BALE_SAFIR_FILE=/path/to/photo.jpg php Examples/uploadAndSendMedia.php
```

نمونهٔ OTP متغیر `BALE_SAFIR_OTP` را هم می‌خواند و نمونهٔ رسانه به مسیر محلی `BALE_SAFIR_FILE` نیاز دارد. راهنمای bash و PowerShell به [فارسی](Examples/README.fa.md) و [انگلیسی](Examples/README.md) موجود است. کلیدها را در محیط یا secret manager نگه دارید و commit نکنید.

## عیب‌یابی

- `MissingApiAccessKeyException`: متغیر `BALE_SAFIR_API_ACCESS_KEY` را تنظیم کنید یا `apiAccessKey` را به `Api` بدهید.
- `MissingBotIdException`: متغیر `BALE_SAFIR_BOT_ID` را تنظیم کنید یا `botId` را به `Api` بدهید.
- `InvalidPhone` (`8`): شماره را بدون جداکننده و با قالب `98...` یا `+98...` بفرستید. شماره موبایل محلی معمولی که با `09` شروع می‌شود خودکار تبدیل می‌شود.
- پاسخ دارای `error_data` ممکن است وضعیت HTTP برابر ۲xx داشته باشد؛ نتیجهٔ آبجکت پاسخ را بررسی کنید.
- قالب پیام باید در پنل تجاری بله از قبل ساخته و تأیید شده باشد و نام‌های `text_fields` باید با قالب هم‌خوانی کنند.

## مستندات سفیر

- [مستندات فنی API](https://docs.bale.ai/safir)
- [صفحهٔ معرفی سفیر](https://bale.ai/safir)
- [SDK مرتبط برای API بازوهای بله](https://github.com/ErfanVahabpour/Bale-Bot-SDK)

مستندات سفیر خطای بارگذاری را گاهی با فیلد `error` و گاهی با `error_data` نشان می‌دهد؛ SDK هر دو گونهٔ پاسخ را می‌پذیرد.
