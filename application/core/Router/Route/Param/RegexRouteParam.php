<?php
namespace Gideon\Router\Route\Param;

class RegexRouteParam extends Base
{
    public function __construct(string $value) 
    {
        $value = trim($value);

        // Determine variable basing if first character is ':'
        if($this->volatile = ($value[0] == ':'))
        {
             // Check for custom defined regex pattern
            if(preg_match('/^:{.+}$/', $value) === 1)
            {
                $this->value = trim(substr($value, 2, -1));
            }
            else 
            {
                $this->name = trim(substr($value, 1));
            }
        } 
        else 
        {
            $this->value = $value;
        }
    }
}