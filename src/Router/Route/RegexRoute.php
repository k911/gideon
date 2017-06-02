<?php
namespace Gideon\Router\Route;

use Gideon\Http\Request;
use Gideon\Router\Route;

class RegexRoute extends Base
{
    /**
     * @param string $value
     */
    protected function paramFrom(string $value): Param
    {
        return new Param\RegexRouteParam($value);
    }

    public function toPattern(array $replacements): string
    {
        $trimmed = [];
        foreach($this->parameters as $param)
        {
            $trimmed[] = ($param->volatile) ?
                ('(' . ($param->value ?? ($replacements[$param->name] ?? $replacements['any'])) . ')') :
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
                $this->getLogger()->info("Modified param :{$param->name} to match regex: {$replacements[$param->name]}");
            }
        }
        return $this;
    }
}
