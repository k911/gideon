<?php
namespace Gideon\Controller;

use Gideon\Router\Route;
use Gideon\Http\Response;
use Gideon\Handler\Error as ErrorHandler;

class Error extends Base
{
    public function NotFound(Route $route)
    {
        return (new Response\Text("Route not found: \n".var_export($route, true)))->setCode(404);
    }

    public function outerStage(ErrorHandler $handler): Response
    {
        // TODO: Finish
        return (new Response\Text("Unexpected error has occured: \n".var_export($handler->getFirst(), true)))->setCode(500);
    }
}
