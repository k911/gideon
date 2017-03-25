<?php
namespace Gideon\Router;

use Gideon\Router;
use Gideon\Handler\Config;
use Gideon\Router\Route;
use Gideon\Debug\Base as Debug;
use Gideon\Http;
use Gideon\Handler\Group\UniformGroup;

/**
 * Config keys used
 * - ROUTER_REPLACEMENTS_DEFAULT
 * - APPLICATION_CONTROLLER_PREFIX
 * - REQUEST_METHODS_SUPPORTED
 */

abstract class Base extends Debug implements Router 
{
    /**
     * @var \ArrayObject[] $routes key => supported http method, value => \ArrayObject container for routes
     */
    protected $routes;
    
    /**
     * @var string[] $replacements matches \Gideon\Router\Route\Param Route\Param->name => Route\Param->value
     */
    protected $replacements;
    
    /**
     * @var string $controllerPrefix namespace of controllers
     */
    protected $controllerPrefix;
    
    /**
     * @var string[] $supportedMethods
     */
    protected $supportedMethods;

    /**
     * Prepare routes for single http method
     * @param string $method
     */
    abstract protected function prepare(string $method);

    public function prepareAll()
    {
        $methods = array_keys($this->routes);
        foreach($methods as $method)
        {
            $this->prepare($method);
        }
    }

    /**
     * Create route object 
     * @param string    $route
     * @param callable  $callback
     */
    abstract protected function routeFrom(string $route, callable $callback = null): Route;

    public function addRoute(string $route, $callback = null, string $method = 'GET'): Route
    {
        $method = strtoupper($method);
        if(!in_array($method, $this->supportedMethods))
            throw new Http\InvalidArgumentException("Cannot add route with unsupported method: $method.");

        // Initialize controller object
        if(is_array($callback) && is_string($callback[0]))
        {   
            $callback[0] = $this->controllerPrefix . $callback[0];
            $callback[0] = new $callback[0]();
        }

        $route = $this->routeFrom($route, $callback);
        if(!$route->isEmpty())
        {
            $this->routes[$method][] = $route;
        } 
        else $this->log("Cannot add empty route: `$route`");

        return $route;
    }

    // // Regex: (\[([^\/]*?)\])(?!.+\])
    // public function addRoutes(string $optionable_route, $callback = null, string $method = null): UniformGroup
    // {

    // }

    public function __construct(Config $config)
    {
        $supportedMethods = $config->get('REQUEST_METHODS_SUPPORTED');
        foreach($supportedMethods as $method)
        {
            $this->routes[$method] = new \ArrayObject();
        }

        $this->replacements = $config->get('ROUTER_REPLACEMENTS_DEFAULT');
        $this->controllerPrefix = $config->get('APPLICATION_CONTROLLER_PREFIX');
        $this->supportedMethods = $supportedMethods;
    }

    public function count(): int 
    {
        $count = 0;
        foreach($this->routes as $mRoutes)
        {
            $count += $mRoutes->count();
        }
        return $count;
    }

    public function isEmpty(): bool
    {
        return ($this->count() == 0);
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        $methods = array_keys($this->routes);
        foreach($methods as $method)
        {
            $debugarr[$method] = $this->routes[$method];
        }
        return $debugarr;
    }
}