<?php
namespace Gideon;

use Gideon\Handler\Config;

interface Router 
{
    public function size(): int;
    public function addRoute(string $route, callable $handler, string $method): Router\Route;
    public function dispatch(Http\Request $request): Router\Route;
    public function __construct(Config $config);
    public function empty(): bool;
}