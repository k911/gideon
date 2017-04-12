<?php
declare(strict_types=1);

namespace Gideon\Exception;

use Throwable;
use Exception as Base;
use ReflectionClass;
use Gideon\Http\ResponseCode;

abstract class ErrorResponseException extends Base
{
    /**
     * @var string in MACRO_CASE
     */
    protected $errorCode;

    /**
     * Converts CamelCase to MACRO_CASE (upper underscore case).
     * Eg. CamelCase => CAMEL_CASE
     * @param string $input sentence in CamelCase
     * @return string sentence in MACRO_CASE
     */
    private function camelToMacroCase(string $input): string
    {
        return strtoupper(preg_replace('~(?!^)([A-Z])~', '_$1', $input));
    }

    public function __construct(string $message, string $code = null, Throwable $previous = null)
    {
        if (is_null($code)) {
            $code = (new ReflectionClass($this))->getShortName();
            $code = str_replace(['Exception', 'Error'], '', $code);
            $code = $this->camelToMacroCase($code);
        }
        $this->errorCode = $code;
        parent::__construct($message, ResponseCode::resolveErrorCode($code), $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
