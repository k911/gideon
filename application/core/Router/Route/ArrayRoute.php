<?php
namespace Gideon\Router\Route;

use Gideon\Router\Route;
use Gideon\Http\Request;

class ArrayRoute extends Base
{
    protected function paramFrom(string $value): Param
    {
        return new Param\ArrayRouteParam($value);
    }

    public function check(Param $param, string $value)
    {
        if($param->volatile)
            return ($param->regex) ? (preg_match('~^' . $param->value . '$~', $value) === 1)  : true;

        return $param->value === $value;
    }

    public function validate(Request $request): bool
    {
        if($this->count() != $request->count())
            return false;

        foreach($request as $i => $value)
        {
            if(!$this->check($this->parameters[$i], $value))
                return false;
        }

        return true;
    }

    public function regex(array $replacements): string
    {
        $trimmed = [];
        foreach($this->parameters as $param)
        {
            $trimmed[] = ($param->volatile) ?
                '(' . ($param->regex ? $param->value : $replacements['any']) . ')' :
                $param->value;
        }
        return implode('/', $trimmed);
    }

    public function where(array $replacements): Route
    {
        foreach($this->parameters as $param)
        {
            if($param->volatile && isset($replacements[$param->name]))
            {
                 $param->value = $replacements[$param->name];
                 $param->regex = true;
            }
        }
        return $this;
    }
}