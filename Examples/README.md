# Runnable examples

These scripts send real requests to Safir. Use a test bot and a phone number you control.

## Configure the environment

Set the organization API access key, numeric bot ID, and destination phone number in your shell. The examples read:

- `BALE_SAFIR_API_ACCESS_KEY`
- `BALE_SAFIR_BOT_ID`
- `BALE_SAFIR_PHONE_NUMBER`

Safir expects an Iranian phone number in `98...` or `+98...` format. The SDK also normalizes an 11-digit local mobile number beginning with `09`.

### Bash

```bash
export BALE_SAFIR_API_ACCESS_KEY='your-organization-access-key'
export BALE_SAFIR_BOT_ID='123456789'
export BALE_SAFIR_PHONE_NUMBER='989123456789'

php Examples/sendMessage.php
```

The OTP example also reads `BALE_SAFIR_OTP`. The media example reads `BALE_SAFIR_FILE` and uploads that local file before sending it:

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

Keep credentials in environment variables or a secret manager; do not commit them. Plain PHP does not load `.env` files unless your application loads them.

Each script creates a fresh random `request_id`. For your own retry logic, reuse the same `request_id` for a retry of the same logical message.
