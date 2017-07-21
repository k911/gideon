<?php
declare(strict_types=1);

namespace Gideon\Filesystem;

use Throwable;

class IOException extends FilesystemException
{
    public function __construct($message, $path, $code = 500, Throwable $previous = null)
    {
        if($code === 404) {
            if($message{-1} !== '.') {
                $message .= '.';
            }
            $message .= ' File does not exist.';
        }
        parent::__construct($message, $path, $code, $previous);
    }
}
