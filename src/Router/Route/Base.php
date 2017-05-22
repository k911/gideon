<?php
namespace Gideon\Router\Route;

use Gideon\Http\Request;
use Gideon\Router\Route;
use Gideon\Debug\Provider as Debug;

abstract class Base extends Debug implements Route
{
    /**
     * @var array $parameters
     */
    protected $parameters;

    /**
     * @var int $size number of $params
     */
    protected $size;

    /**
     * @var int[] $variables indexes of $params which are variables
     */
    protected $variables;

    /**
     * @var callable $callback function
     */
    private $callback;

    /**
     * Creates param specific for route
     * @param string $value
     * @return \Gideon\Router\Route\Param
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
        foreach ($values as $value) {
            $value = trim($value);
            if (!empty($value) || $value === '0') {
                $params[] = $param = $this->paramFrom($value);
                if ($param->volatile) {
                    $vars[] = $index;
                }
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
        [$parameters, $size, $variables] = $this->parse($route);

        $this->parameters = $parameters;
        $this->size = $size;
        $this->variables = $variables;
        $this->callback = $callback;
    }

    public function map(Request $request): array
    {
        $data = [];
        foreach ($this->variables as $index) {
            $data[] = $request[$index];
        }
        return $data;
    }

    public function isEmpty(): bool
    {
        return $this->size == 0;
    }

    public function variables(): int
    {
        return count($this->variables);
    }

    public function count(): int
    {
        return $this->size;
    }

    public function getCallback(): ?callable
    {
        return $this->callback;
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return [
            'params' => $this->parameters,
            'vars' => $this->variables,
            'callback' => $this->callback
        ];
    }
}
