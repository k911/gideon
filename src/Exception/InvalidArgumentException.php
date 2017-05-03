<?php
declare(strict_types=1);

namespace Gideon\Exception;

use Psr\Log\LogLevel;
use InvalidArgumentException as Base;

class InvalidArgumentException extends Base implements Any
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
