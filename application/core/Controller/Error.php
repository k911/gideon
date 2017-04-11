<?php
namespace Gideon\Controller;

use Gideon\Router\Route;
use Gideon\Http\Response;

class Error extends Base 
{
    public function NotFound(Route $route)
    {
        return (new Response\Text("Route not found: \n".var_export($route,true)))->setCode(404);
    }
}