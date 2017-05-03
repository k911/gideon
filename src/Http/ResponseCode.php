<?php
declare(strict_types=1);
namespace Gideon\Http;

class ResponseCode {
    private function __construct() {} // Static class TODO: or not?

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
