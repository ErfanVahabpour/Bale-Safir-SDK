<?php

require dirname(__DIR__).'/vendor/autoload.php';

use EFive\Bale\Safir\Api;

$phoneNumber = getenv('BALE_SAFIR_PHONE_NUMBER');
$otp = getenv('BALE_SAFIR_OTP');
if (! is_string($phoneNumber) || $phoneNumber === '' || ! is_string($otp) || $otp === '') {
    throw new RuntimeException('Set BALE_SAFIR_PHONE_NUMBER and BALE_SAFIR_OTP before running this example.');
}

$response = (new Api())->sendMessage([
    'request_id' => bin2hex(random_bytes(16)),
    'phone_number' => $phoneNumber,
    'message_data' => [
        'otp_message' => ['otp' => $otp],
    ],
]);

if (! $response->isSuccessful()) {
    foreach ($response->getErrorData() as $error) {
        fwrite(STDERR, sprintf("%s: %s\n", $error->getCodeName() ?? 'Safir error', $error->getDescription() ?? ''));
    }

    exit(1);
}

printf("OTP message sent: %s\n", $response->getMessageId());
