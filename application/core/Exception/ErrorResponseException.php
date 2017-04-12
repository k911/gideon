<?php
declare(strict_types=1);

namespace Gideon\Exception;

use Throwable;
use Exception as Base;

class ErrorResponseException extends Base
{
    /**
     * Gets response status code from custom sting code
     */
    private function resolveResponseStatusCode(string $code): int
    {


    }

    public function __construct(string $message, string $code = null, Throwable $previous = null)
    {
        if(is_null($code))
        {
            $code = get_class($this);
        }
        parent::__construct($message, $code, $previous);
    }
}
