<?php
declare(strict_types=1);

namespace Gideon\Exception;

use Throwable;
use RuntimeException;

class IOException extends RuntimeException 
{
    /**
     * @var string $path to file which caused IOException
     */
    private $path;

    /**
     * @param string $message
     * @param string $path to error file/directory
     * @param int $code
     * @param Throwable $previous
     */
    public function __construct(string $message, string $path, int $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    } 

    /**
     * @return string
     */
    public function getPath(): string
    {
        return realpath($this->path);
    }

}