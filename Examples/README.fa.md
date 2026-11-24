# نمونه‌های اجرایی

این اسکریپت‌ها درخواست واقعی به سفیر می‌فرستند. از بازوی آزمایشی و شماره‌ای که اجازهٔ ارسال به آن را دارید استفاده کنید.

## تنظیم محیط

کلید دسترسی سازمان، شناسه عددی بازو و شماره مقصد را در shell تنظیم کنید. نمونه‌ها این متغیرها را می‌خوانند:

- `BALE_SAFIR_API_ACCESS_KEY`
- `BALE_SAFIR_BOT_ID`
- `BALE_SAFIR_PHONE_NUMBER`

شمارهٔ ایران را با قالب `98...` یا `+98...` بدهید. SDK شمارهٔ موبایل محلی ۱۱ رقمی را که با `09` شروع شود نیز تبدیل می‌کند.

### Bash

```bash
export BALE_SAFIR_API_ACCESS_KEY='your-organization-access-key'
export BALE_SAFIR_BOT_ID='123456789'
export BALE_SAFIR_PHONE_NUMBER='989123456789'

php Examples/sendMessage.php
```

نمونهٔ OTP متغیر `BALE_SAFIR_OTP` را می‌خواند. نمونهٔ رسانه ابتدا مسیر محلی `BALE_SAFIR_FILE` را بارگذاری می‌کند و سپس پیام می‌فرستد:

```bash
BALE_SAFIR_OTP='123456' php Examples/sendOtp.php
BALE_SAFIR_FILE='/path/to/photo.jpg' php Examples/uploadAndSendMedia.php
```

### PowerShell

```powershell
$env:BALE_SAFIR_API_ACCESS_KEY = 'your-organization-access-key'
$env:BALE_SAFIR_BOT_ID = '123456789'
$env:BALE_SAFIR_PHONE_NUMBER = '989123456789'

php Examples/sendMessage.php
$env:BALE_SAFIR_OTP = '123456'
php Examples/sendOtp.php
$env:BALE_SAFIR_FILE = 'C:\path\to\photo.jpg'
php Examples/uploadAndSendMedia.php
```

کلید را در متغیر محیطی یا secret manager نگه دارید و commit نکنید. PHP ساده فایل `.env` را خودکار نمی‌خواند؛ برنامه باید آن را بارگذاری کند.

هر نمونه یک `request_id` تصادفی تازه می‌سازد. در منطق retry برنامهٔ خودتان، برای تلاش مجدد همان پیام همان `request_id` را دوباره استفاده کنید.
