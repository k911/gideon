<?php
declare(strict_types=1);

namespace Gideon\Exception;

use Psr\Log\LogLevel;

abstract class Failure extends ErrorException
{
    public function getLogLevel(): string
    {
        return LogLevel::ALERT;
    }
}
