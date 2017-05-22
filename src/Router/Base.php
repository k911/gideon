<?php
namespace Gideon\Router;

use ArrayObject;
use Gideon\Router;
use Gideon\Config;
use Gideon\Router\Route;
use Gideon\Debug\Provider as Debug;
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
     * @var Route\EmptyRoute $defaultRoute default result of dispatch()
     */
    protected $defaultRoute;

    /**
     * Prepare routes for single http method
     * @param string $method
     */
    abstract protected function prepare(string $method);

    public function prepareAll()
    {
        $methods = array_keys($this->routes);
        foreach ($methods as $method) {
            $this->prepare($method);
        }
    }

    public function addRoute(string $route, $callback = null, string $method = null): Route
    {
        $method = isset($method) ? strtoupper($method) : 'GET';
        if (!in_array($method, $this->supportedMethods)) {
            throw new Http\InvalidArgumentException("Cannot add route with unsupported method: $method.");
        }

        // Initialize controller object
        if (is_array($callback) && is_string($callback[0])) {
            $callback[0] = $this->controllerPrefix . $callback[0];
            $callback[0] = new $callback[0]();
        }

        $route = $this->createRouteFrom($route, $callback);
        $this->routes[$method][] = $route;
        return $route;
    }

    // TODO: addMultipleRoutes()

    public function __construct(Config $config)
    {
        $supportedMethods = $config->get('REQUEST_METHODS_SUPPORTED');
        foreach ($supportedMethods as $method) {
            $this->routes[$method] = new ArrayObject();
        }

        $this->replacements = $config->get('ROUTER_REPLACEMENTS_DEFAULT');
        $this->controllerPrefix = $config->get('APPLICATION_CONTROLLER_PREFIX');
        $this->supportedMethods = $supportedMethods;
        $this->defaultRoute = new Route\EmptyRoute();
    }

    public function count(): int
    {
        $count = 0;
        foreach ($this->routes as $mRoutes) {
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
        foreach ($methods as $method) {
            $debugarr[$method] = $this->routes[$method];
        }
        return $debugarr;
    }
}
