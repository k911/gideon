<?php
namespace Gideon\Router;

use Gideon\Router;
use Gideon\Http\Request;
use Gideon\Handler\Config;
use Gideon\Debug\Base as Debug;

class LoopRouter extends Debug implements Router
{

    /**
     * @var array       $routes key => HTTP_METHOD, values => Gideon\Router\ArrayRoute[]
     * @var string[]    $replacements key => name to replace in RegexRoute\Param()->value with regex
     * @var bool[]      $prepared
     */
    private $routes;
    private $replacements;
    private $prepared;

    /**
     * @param Request
     * @return Router\Route | EmptyRoute when not matches
     */
    public function dispatch(Request $request): Route
    {
        $method = $request->method();

        // Secure from situation when trying to dispatch having no routes for method
        if(!empty($this->routes[$method]))
        {
            if(empty($this->prepared[$method]))
                $this->prepare($method);

            $method_routes = $this->routes[$method];
            foreach($method_routes as $route)
            {
                if($route->validate($request))
                    return $route;
            }
        }
        return new Route\EmptyRoute();
    }
    
    public function prepare(string $method)
    {
        foreach($this->routes[$method] as $route)
        {
            $route->where($this->replacements);
        }
        $this->prepared[$method] = true;
    }

    public function prepareAll()
    {
        $methods = array_keys($this->routes);
        foreach($methods as $method)
        {
            $this->prepare($method);
        }
    }

    public function size(): int 
    {
        $count = 0;
        foreach($this->routes as $method_routes)
            $count += count($method_routes);
        return $count;
    }

    public function empty(): bool
    {
        return empty($this->routes);
    }

    public function addRoute(string $route, callable $handler = null, string $method = 'GET'): Router\Route
    {
        $method = strtoupper($method);
        $route = new Route\ArrayRoute($route, $handler);
        if(!$route->empty())
            $this->routes[$method][] = $route;
        
        return $route;
    }

    public function __construct(Config $config) 
    {
        $this->replacements = $config->get('FAST_ROUTER_REPLACEMENTS_DEFAULT');
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        $debugarr = [];

        $methods = array_keys($this->routes);
        foreach($methods as $method)
        {
            $debugarr[$method] = $this->routes[$method];
        }
        return $debugarr;
    }
}