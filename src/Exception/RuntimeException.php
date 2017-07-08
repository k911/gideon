<?php
declare(strict_types=1);

namespace Gideon\Exception;

use Psr\Log\LogLevel;
use RuntimeException as Base;

class RuntimeException extends Base implements Any
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
