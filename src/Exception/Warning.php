<?php
declare(strict_types=1);

namespace Gideon\Exception;

use Psr\Log\LogLevel;

final class Warning extends ErrorException
{
    public function getLogLevel(): string
    {
        return LogLevel::WARNING;
    }
}
