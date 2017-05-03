<?php
declare(strict_types=1);

namespace Gideon\Exception;

use Psr\Log\LogLevel;

final class Fatal extends ErrorException
{
    public function getLogLevel(): string
    {
        return LogLevel::EMERGENCY;
    }
}
