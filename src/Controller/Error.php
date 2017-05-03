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
    public function router(ErrorHandler $handler, array $arguments = null): Response
    {
        throw new Exception();
        if(($err = $handler->getFirst()) instanceof NotFoundException)
        {
            $handler->pop();
            return (new Response\Text("{{PARAM_MESSAGE}}\n ..with arguments: {{PARAM_ARGS}}"))
                ->setCode(404)
                ->bindParam('MESSAGE', $err->getMessage())
                ->bindParam('ARGS', json_encode($arguments ?? [], JSON_PRETTY_PRINT));
        }
    }

    public function controller(ErrorHandler $handler, Controller $controller): Response
    {
        $html = '';
        foreach($handler->getAll() as $error)
        {
            $html .= "<table>{$error->xdebug_message}</table>";
        }

        $response = new Response\Text("Executing {{PARAM_CONTROLLER}}->{{PARAM_ACTION}} resulted in errors:\n{{PARAM_ERRORS_HTML}}");
        $response->setCode(500);
        $response->setType('text/html');
        $response->bindParams([
            'ERRORS_HTML' => $html,
            'CONTROLLER' => (new ReflectionClass($controller))->getShortName(),
            'ACTION' => $action]);
        $handler->clear();
        return $response;
    }

    public function failure(ErrorHandler $handler): Response
    {
        $html = '';
        foreach($handler->getAll() as $error)
        {
            $html .= "<table>{$error->xdebug_message}</table>";
        }

        $response = new Response\Text("Complete failure errors:\n{{PARAM_ERRORS_HTML}}");
        $response->setCode(500);
        $response->setType('text/html');
        $response->bindParam('ERRORS_HTML', $html);
        $handler->clear();
        return $response;
    }

    public function unhandled(ErrorHandler $handler, Response $response): Response
    {
        $errors = $handler->getAllTransformed();
        if($response instanceof Response\Text)
        {
            $txt = json_encode($errors, JSON_PRETTY_PRINT);
            $responseException = (new Response\Text("Some errors has not been handled properly:\n{{PARAM_ERRORS}}"))
                ->bindParam('ERRORS', $txt)
                ->setCode(500);
            return $response->mergeWith($responseException);
        }
        return $response->setCode(500);
    }
}
