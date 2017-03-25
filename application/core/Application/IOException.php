<?php
namespace Gideon\Application;

class IOException extends \RuntimeException 
{
    /**
     * @var string $path to file which caused IOException
     */
    private $path;

    public function __construct(string $path, string $message = "", int $code = 0, \Exception $previous = null)
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