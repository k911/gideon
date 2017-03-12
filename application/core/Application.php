<?php

namespace Gideon;

use Gideon\Debug\Base as Debug;
use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Http\Request;
use Gideon\Renderer\Response;
use Gideon\Database\Connection;
use Gideon\Router\Route\EmptyRoute;

/**
 * Config keys used:
 * - VIEW_ERROR
 * - APPLICATION_CLOSURE_ACTION_NAME
 * - APPLICATION_CONTROLLER_PREFIX
 */
class Application extends Debug
{
    protected $connection;
    protected $config;
    protected $locale;
    protected $router;
    protected $request;
    protected $renderer;

    public function __construct(Config $config, Router $router)
    {
        $locale = new Locale($config);
        $request = new Request($config);
        $connection = new Connection\MySQL($config);

        $renderer = new Renderer($config, $locale);
        $renderer->uri = $request->uri();
        $renderer->method = $request->method();

        $this->config = $config;
        $this->router = $router;
        $this->locale = $locale;
        $this->request = $request;
        $this->renderer = $renderer;
        $this->connection = $connection;
    }

    public function error(int $id): Response
    {
        return ($this->request->method() === 'GET') ? 
            (new Response\View($this->config->get('VIEW_ERRROR') . $id, $id)) :
            (new Response\JSON(['error_id' => $id], $id));
    }

    protected function exec(callable $handler, array $args)
    {
        // Init some things depending if using Gideon\Controller or anonymous function (\Closure)
        $controller_name = $action = '';
        if($handler instanceof \Closure)
        {
            $controller_name = substr(get_class($handler), 1);
            $action = $this->config->get('APPLICATION_CLOSURE_ACTION_NAME');
        } 
        elseif (is_array($handler) && $handler[0] instanceof Controller)
        {
            list($controller, $action) = $handler;
            $controller->init($this->config, $this->locale, $this->request, $this->connection);
            $controller_name = str_replace($this->config->get('APPLICATION_CONTROLLER_PREFIX'), '', get_class($controller));
        }
        else 
        {
            $controller_name = get_class($handler);
            $this->log("Unrecognized callable: $controller_name");
        }

        $this->renderer->controller = $controller_name;
        $this->renderer->action = $action;

        // Execute function
        try 
        {
            $this->renderer->init(call_user_func_array($handler, $args));
        } 
        catch (\Throwable $thrown)
        {
            $this->renderer->init($this->error(500));
            $this->logException($thrown);
        }
     }
        

    public function run()
    {
        // Open neeeded things
        session_start();

        $route = $this->router->dispatch($this->request);
        if($route instanceof EmptyRoute)
        {
            $this->response = $this->error(404);
        }
        else
        {
            $args = $route->map($this->request);
            $handler = $route->callback();
            $this->exec($handler, $args);
        }

        // Close not needed things
        $this->connection->close();
        session_write_close();
    }

    public function render()
    {
        $this->renderer->render();
    }

    protected function getDebugProperties(): array 
    {
        return [
            'config' => $this->config,
            'locale' => $this->locale,
            'router' => $this->router,
            'request' => $this->request,
            'renderer' => $this->renderer,
        ];
    }

}