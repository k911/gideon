<?php
namespace Gideon\Router\Route\Param;

use Gideon\Router\Route\Param as Base;

class ArrayRouteParam extends Base
{
    /**
     * @var string $regex
     */
    public $regex;

    public function __construct(string $param) 
    {
        $param = trim($param);

        // Determine variable basing if first character is ':'
        if($this->volatile = ($param[0] == ':'))
        {
             // Check for custom defined regex pattern
            if($this->regex = (preg_match('/^:{.+}$/', $param)) === 1)
            {
                $this->value = trim(substr($param, 2, -1));
            }
            else 
            {
                $this->name = trim(substr($param, 1));
            }
        } 
        else 
        {
            $this->value = $param;
        }
    }
}