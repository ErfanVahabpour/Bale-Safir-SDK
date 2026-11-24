<?php

namespace EFive\Bale\Safir\Exceptions;

final class MissingBotIdException extends SafirException
{
    public function __construct()
    {
        parent::__construct('A Safir bot_id is required. Set BALE_SAFIR_BOT_ID or include bot_id in the request.');
    }
}
