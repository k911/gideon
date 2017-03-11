<?php
namespace Gideon\Router\Route;

use Gideon\Http\Request;
use Gideon\Router\Route;
use Gideon\Debug\Base as Debug;

abstract class Base extends Debug implements Route 
{
    /**
     * @var array   $params
     * @var int     $size number of $params
     * @var int[]   $vars indexes of $params which are variables
     * @var callable $callback function
     */
    protected $parameters;
    protected $size;
    protected $variables;
    private $callback;

    /**
     * Creates param specific for route
     * @param string $value
     * @param Gideon\Router\Route\Param
     */
    abstract protected function paramFrom(string $value): Param;

    /** 
     * Parses $route to params
     * @param string $route
     * @return array [0 => params, 1 => size, 2 => indexes of volatile params]
     */
    protected function parse(string $route): array
    {
        $values = explode('/', $route);
        $params = [];
        $vars = [];
        $index = 0;
        foreach($values as $value)
        {
            $value = trim($value);
            if(!empty($value) || $value === '0') 
            {
                $params[] = $param = $this->paramFrom($value);
                if($param->volatile)
                    $vars[] = $index;
                ++$index;
            }
        }
        return [$params, $index, $vars];
    }

    /**
     * @param string    $route
     * @param callable  $callback
     */
    public function __construct(string $route, callable $callback = null)
    {
        // TODO: PHP 7.1 []
        list($this->parameters, $this->size, $this->variables) = $this->parse($route);
        $this->callback = $callback;
    }

    public function map(Request $request): array
    {
        $data = [];
        foreach($this->variables as $index)
        {
            $data[] = $request[$index];
        }
        return $data;
    }

    public function empty(): bool
    {
        return $this->size == 0;
    }

    public function variables(): int 
    {
        return count($this->variables);
    }

    public function size(): int 
    {
        return $this->size;
    }

    public function callback(): callable
    {
        return is_null($this->callback) ? (function(){}) : $this->callback;
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return [
            'params' => $this->parameters,
            'vars' => $this->variables,
            'handler' => $this->handler
            ];
    }
}