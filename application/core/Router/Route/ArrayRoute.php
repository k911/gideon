<?php
namespace Gideon\Router\Route;

use Gideon\Http\Request;
use Gideon\Router\Route;
use Gideon\Debug\Base as Debug;

class ArrayRoute extends Debug implements Route
{

    /**
     * @var ArrayRoute\Param[]  $params
     * @var int[]               $vars
     * @var callable            $handler 
     * @var int                 $size
     */
    protected $params;
    protected $vars;
    protected $handler;
    protected $size;

    protected function parse(string $route)
    {
        $index = 0;
        $values = explode('/', $route);
        foreach($values as $value)
        {
            $value = trim($value);
            if(!empty($value) || $value === '0') 
            {
                $param = $this->params[] = new ArrayRoute\Param($value);
                if($param->volatile)
                    $this->vars[] = $index;
                ++$index;
            }
        }
        $this->size = $index;
    }

    /**
    * @param string $route 
    * @param callable $handler
    */
    public function __construct(string $route, callable $handler = null)
    {
        $this->parse($route);
        $this->handler = $handler;
    }

    public function empty(): bool
    {
        return empty($this->params);
    }

    public function size(): int
    {
        return $this->size;
    }

    public function variables(): int
    {
        return count($this->vars);
    }

    /**
     * @return string[]
     */
    public function map(Request $request): array
    {
        $data = [];
        if(!empty($this->vars))
        {
            foreach($this->vars as $index)
            {
                $data[] = $request[$index];
            }
        }
        return $data;
    }

    public function check(ArrayRoute\Param $param, string $value)
    {
        if($param->volatile)
            return ($param->regex) ? (preg_match('~^' . $param->value . '$~', $value) === 1)  : true;

        return $param->value === $value;
    }

    public function validate(Request $request): bool
    {
        if($this->size() != $request->size())
            return false;

        foreach($request as $i => $value)
        {
            if(!$this->check($this->params[$i], $value))
                return false;
        }

        return true;
    }

    public function regex(array $replacements): string
    {
        $trimmed = [];
        foreach($this->params as $param)
        {
            $trimmed[] = ($param->volatile) ?
                '(' . ($param->regex ? $param->value : $replacements['any']) . ')' :
                $param->value;
        }
        return implode('/', $trimmed);
    }

    public function where(array $replacements): Route
    {
        foreach($this->params as $param)
        {
            if($param->volatile && isset($replacements[$param->name]))
            {
                 $param->value = $replacements[$param->name];
                 $param->regex = true;
            }
        }
        return $this;
    }

    /**
     * @return callable or null
     */
    public function handler(): callable 
    {
        return $this->handler;
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return ['params' => $this->params,
                'vars' => $this->vars,
                'handler' => $this->handler];
    }
}