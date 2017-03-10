<?php
namespace Gideon\Router\Route\ArrayRoute;

class Param 
{
    /**
     * @var string  $name
     * @var string  $value
     * @var bool    $volatile
     */
    public $name;
    public $value;
    public $volatile;
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