<?php

namespace EFive\Bale\Safir\Exceptions;

final class MissingApiAccessKeyException extends SafirException
{
    public function __construct()
    {
        parent::__construct('Safir API access key is required. Set BALE_SAFIR_API_ACCESS_KEY or pass the key to Api.');
    }
}
