<?php
namespace Gideon\Router\Route;

abstract class Param 
{
    /**
     * @var string  $name
     * @var string  $value
     * @var bool    $volatile
     */
    public $name;
    public $value;
    public $volatile;

    /** 
     * Parses $param to set properties
     * @param string $param
     */
    abstract public function __construct(string $param);
};