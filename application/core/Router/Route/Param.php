<?php
namespace Gideon\Router\Route;

interface Param 
{
    /**
     * Sets object properites
     * @param string $value
     */
     public function __construct(string $value);
};