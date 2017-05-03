<?php
declare(strict_types=1);

namespace Gideon\Exception;

use Error as Base;
use Psr\Log\LogLevel;

abstract class Error extends Base implements Any
{
    public function getLogLevel(): string
    {
        return LogLevel::CRITICAL;
    }

    public function getGetters(): array
    {
        return ['logLevel'];
    }
}
