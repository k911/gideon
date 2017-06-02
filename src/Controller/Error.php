<?php
namespace Gideon\Controller;

use ReflectionClass;
use Gideon\Controller;
use Gideon\Router\Route;
use Gideon\Http\Response;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Http\NotFoundException;

class Error extends Base
{
    public function router(ErrorHandler $handler): Response
    {
        [$i, $error] = $handler->findOne('Gideon\Http\NotFoundException');
        if(isset($error))
        {
            $handler->pop($i);
            $params = json_encode($this->params->getAll(), JSON_PRETTY_PRINT);
            return (new Response\Text("{{PARAM_MESSAGE}}\n ..with data: {{PARAM_ARGS}}"))
                ->setCode(404)
                ->bindParam('MESSAGE', $error->getMessage())
                ->bindParam('ARGS', $params);
        }
    }

    public function controller(ErrorHandler $handler, Controller $controller, string $action): Response
    {
        $html = '';
        foreach($handler->findAll() as $i => $error)
        {
            $html .= "<table>{$error->xdebug_message}</table><br />";
        }

        $response = new Response\Text("<p><h2>Executing `{{PARAM_CONTROLLER}}->{{PARAM_ACTION}}` resulted in errors:</h2></p>{{PARAM_ERRORS_HTML}}");
        $response->setCode(500);
        $response->setType('text/html');
        $response->setParams([
            'ERRORS_HTML' => $html,
            'CONTROLLER' => (new ReflectionClass($controller))->getShortName(),
            'ACTION' => $action]);
        $handler->clear();
        return $response;
    }

    public function failure(ErrorHandler $handler): Response
    {
        $html = '';
        foreach($handler->findAll() as $i => $error)
        {
            $html .= "<table>{$error->xdebug_message}</table><br />";
        }

        $response = new Response\Text("<p><h2>Complete failure errors:</h2></p>{{PARAM_ERRORS_HTML}}");
        $response->setCode(500);
        $response->setType('text/html');
        $response->bindParam('ERRORS_HTML', $html);
        $handler->clear();
        return $response;
    }

    public function unhandled(ErrorHandler $handler, Response $response): Response
    {
        if($response instanceof Response\Text)
        {
            $html = '';
            foreach($handler->findAll() as $i => $error)
            {
                $html .= "<table>{$error->xdebug_message}</table><br />";
            }
            $response = $response->mergeWith((new Response\Text("<p><h2>Some errors has not been handled properly:</h2></p>{{PARAM_ERRORS}}"))
                ->bindParam('ERRORS', $html)
                ->setType('text/html'));

            $handler->clear();
        }

        return $response->setCode(500);
    }
}
