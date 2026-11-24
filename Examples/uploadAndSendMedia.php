<?php

require dirname(__DIR__).'/vendor/autoload.php';

use EFive\Bale\Safir\Api;
use EFive\Bale\Safir\FileUpload\InputFile;

$phoneNumber = getenv('BALE_SAFIR_PHONE_NUMBER');
$filePath = getenv('BALE_SAFIR_FILE');
if (! is_string($phoneNumber) || $phoneNumber === '' || ! is_string($filePath) || $filePath === '') {
    throw new RuntimeException('Set BALE_SAFIR_PHONE_NUMBER and BALE_SAFIR_FILE before running this example.');
}

$api = new Api();
$upload = $api->uploadFile(InputFile::fromPath($filePath));

if (! $upload->isSuccessful()) {
    fwrite(STDERR, "Safir did not return a file_id for the upload.\n");
    exit(1);
}

$sent = $api->sendMessage([
    'request_id' => bin2hex(random_bytes(16)),
    'phone_number' => $phoneNumber,
    'message_data' => [
        'message' => [
            'text' => 'Media uploaded with Bale Safir SDK',
            'file_id' => $upload->getFileId(),
        ],
    ],
]);

if (! $sent->isSuccessful()) {
    foreach ($sent->getErrorData() as $error) {
        fwrite(STDERR, sprintf("%s: %s\n", $error->getCodeName() ?? 'Safir error', $error->getDescription() ?? ''));
    }

    exit(1);
}

printf("Media message sent: %s\n", $sent->getMessageId());
