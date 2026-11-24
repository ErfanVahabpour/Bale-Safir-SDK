<?php

require dirname(__DIR__).'/vendor/autoload.php';

use EFive\Bale\Safir\Api;

$phoneNumber = getenv('BALE_SAFIR_PHONE_NUMBER');
if (! is_string($phoneNumber) || $phoneNumber === '') {
    throw new RuntimeException('Set BALE_SAFIR_PHONE_NUMBER before running this example.');
}

$response = (new Api())->sendMessage([
    // Reuse this ID if retrying this same logical send.
    'request_id' => bin2hex(random_bytes(16)),
    'phone_number' => $phoneNumber,
    'message_data' => [
        'message' => [
            'text' => 'Hello from Bale Safir SDK',
        ],
    ],
]);

if (! $response->isSuccessful()) {
    foreach ($response->getErrorData() as $error) {
        fwrite(STDERR, sprintf("%s: %s\n", $error->getCodeName() ?? 'Safir error', $error->getDescription() ?? ''));
    }

    exit(1);
}

printf("Message sent: %s\n", $response->getMessageId());
