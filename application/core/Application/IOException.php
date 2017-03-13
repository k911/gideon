<?php
namespace Gideon\Application;

class IOException extends \RuntimeException 
{
    private $path;

    public function __construct(string $path, string $message = "", int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    } 

    public function getPath(): string
    {
        return realpath($this->path);
    }

}