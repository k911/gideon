<?php
declare(strict_types=1);

namespace Gideon\Http;

use Throwable;
use ReflectionClass;
use Gideon\Exception\Exception;

abstract class ResponseException extends Exception
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
            $code = str_replace('Exception', '', $code);
            $code = $this->camelToMacroCase($code);
        }
        $this->errorCode = $code;
        parent::__construct($message, self::resolveErrorCode($code), $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getGetters(): array
    {
        return array_merge(parent::getGetters(), ['errorCode']);
    }

    /**
     * Gets response status code from custom sting code
     * @link http://www.restapitutorial.com/httpstatuscodes.html
     * @param string $code unique name in MACRO_CASE
     * @return int http status response code
     */
    public static function resolveErrorCode(string $code): int
    {
        switch ($code) {
            case 'BAD_REQUEST':
                return 400;
            case 'NOT_FOUND':
                return 404;
            case 'DEFAULT':
            case 'INTERNAL_SERVER_ERROR':
            default:
                return 500;
        }
    }
}
