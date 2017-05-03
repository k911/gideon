<?php
declare(strict_types=1);

namespace Gideon\Exception;

use Psr\Log\LogLevel;

abstract class Breakdown extends Failure
{
    public function getLogLevel(): string
    {
        return LogLevel::EMERGENCY;
    }
}
