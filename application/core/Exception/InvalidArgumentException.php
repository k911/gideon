<?php
declare(strict_types=1);

namespace Gideon\Exception;

use Throwable;
use InvalidArgumentException as Base;

class InvalidArgumentException extends Base
{
    public function __construct(string $message, int $code = 400, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}