<?php

namespace EFive\Bale\Safir\Laravel\Facades;

use EFive\Bale\Safir\Api;
use EFive\Bale\Safir\Objects\SendMessageResponse;
use EFive\Bale\Safir\Objects\UploadFileResponse;
use EFive\Bale\Safir\SafirManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Api client(?string $name = null)
 * @method static bool hasClient(string $name)
 * @method static array getClients()
 * @method static string getDefaultClientName()
 * @method static SendMessageResponse sendMessage(array $params)
 * @method static UploadFileResponse uploadFile(\EFive\Bale\Safir\FileUpload\InputFile|string $file, ?string $filename = null, ?string $mimeType = null)
 */
final class Safir extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SafirManager::class;
    }
}
