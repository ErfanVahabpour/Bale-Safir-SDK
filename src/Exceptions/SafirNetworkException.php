<?php

namespace EFive\Bale\Safir\Exceptions;

final class SafirNetworkException extends SafirException
{
    public function __construct()
    {
        parent::__construct('The request to the Safir API failed because of a network error.');
    }
}
