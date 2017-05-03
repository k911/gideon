<?php
declare(strict_types=1);

namespace Gideon\Exception;

use ErrorException as Base;
use Psr\Log\LogLevel;

abstract class ErrorException extends Base implements Any
{
    public function getLogLevel(): string
    {
        return LogLevel::ERROR;
    }

    public function getGetters(): array
    {
        return ['logLevel'];
    }
}
