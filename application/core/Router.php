<?php
namespace Gideon;

use Gideon\Handler\Config;
use Gideon\Http\Request;
use Gideon\Router\Route;

interface Router 
{
    /**
     * Return number of routes in all methods
     * @return int
     */
    public function size(): int;
    
    /**
     * Adds single routes
     * @param string    $route
     * @param mixed     $handler can be: \Closure, callable or ['ControllerName', 'method'] 
     * @param string    $method http
     * @return Gideon\Router\Route created route
     */
    public function addRoute(string $route, $handler = null, string $method = 'GET'): Route;
    
    /**
     * Matches request with available routes
     * @param Gideon\Http\Reqest
     * @return Gideon\Router\Route
     */
    public function dispatch(Request $request): Route;

    /**
     * Checks if router has no routes
     * @return bool
     */
    public function empty(): bool;
}