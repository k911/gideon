<?php
namespace Gideon;

use Countable;
use Gideon\Application\Config;
use Gideon\Http\Request;
use Gideon\Router\Route;

interface Router extends Countable
{
    /**
     * Adds single routes
     * @param string $route
     * @param string|callable $callback
     * @param string $method http method
     * @return \Gideon\Router\Route created route
     */
    public function addRoute(string $route, $callback = null, string $method = null): Route;

    /**
     * Matches request with available routes
     * @param \Gideon\Http\Request
     * @return \Gideon\Router\Route If not found returns Gideon\Router\Route\EmptyRoute instance
     */
    public function dispatch(Request $request): Route;

    /**
     * Create compatible route
     * @param string $route
     * @param callable $callback proper route object
     */
    public function createRouteFrom(string $route, callable $callback = null): Route;

    /**
     * Checks if router has no routes
     * @return bool
     */
    public function isEmpty(): bool;
}
