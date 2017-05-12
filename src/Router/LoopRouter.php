<?php
namespace Gideon\Router;

use Gideon\Http\Request;
use Gideon\Application\Config;
use Gideon\Router\Route\EmptyRoute;

class LoopRouter extends Base
{

    /**
     * @var bool[] $prepared methods that are ready to dispatch
     */
    private $prepared;

    public function dispatch(Request $request): Route
    {
        $method = $request->method();

        if(!$this->prepared[$method])
            $this->prepare($method);

        $routes = $this->routes[$method];
        foreach($routes as $route)
        {
            if($route->validate($request))
                return $route;
        }

        return $this->defaultRoute;
    }

    public function __construct(Config $config)
    {
        parent::__construct($config);
        foreach($this->supportedMethods as $method)
        {
            $this->prepared[$method] = false;
        }
    }

    protected function prepare(string $method)
    {
        foreach($this->routes[$method] as $route)
        {
            $route->where($this->replacements);
        }
        $this->prepared[$method] = true;
    }

    public function createRouteFrom(string $route, callable $callback = null): Route
    {
        return new Route\ArrayRoute($route, $callback);
    }
}
