<?php
declare(strict_types=1);

namespace Gideon\Exception;

use Psr\Log\LogLevel;

final class Notice extends ErrorException
{
    public function getLogLevel(): string
    {
        return LogLevel::NOTICE;
    }
}
