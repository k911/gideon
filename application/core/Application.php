<?php

namespace Gideon;

use Gideon\Debug\Base as Debug;
use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Http\Request;
use Gideon\Renderer\Response;

/**
 * Config keys used:
 * - VIEW_ERROR
 * - APPLICATION_CLOSURE_ACTION_NAME
 */
class Application extends Debug
{
    //TODO: $db[connection];
    protected $config;
    protected $locale;
    protected $router;
    protected $request;
    protected $renderer;
    //TODO: $error_keeper;

    public function __construct(Config $config, Router $router)
    {
        // TODO: if($config->get('DBCONNECTION_GET')) $this->db = new DBConnection($config);
        // Inside constructor of DB ask if extend config from db

        $locale = new Locale($config);
        $request = new Request($config);
        $renderer = new Renderer($config, $locale);
        $renderer->uri = $request->uri();
        $renderer->method = $request->method();

        $this->config = $config;
        $this->router = $router;
        $this->locale = $locale;
        $this->request = $request;
        $this->renderer = $renderer;
    }

    public function error(int $id): Response
    {
        return ($this->request->method() === 'GET') ? 
            (new Response\View($this->config->get('VIEW_ERRROR') . $id, $id)) :
            (new Response\JSON(['error_id' => $id], $id));
    }

    protected function executioner(callable $handler, array $args)
    {
        // Init some things depending if using Gideon\Controller or anonymous function (\Closure)
        $anon = $handler instanceof \Closure;
        if(!$anon)
        {
            // init controller
            // TODO: add db if implemented
            $handler[0] = new $handler[0]($this->config, $this->locale, $this->request);
        }
        $class_name = get_class($anon ? $handler : $handler[0]);
        $this->renderer->controller = substr($controller_name, strrpos($controller_name, '\\') + 1);
        $this->renderer->action = $anon ? $this->config->get('APPLICATION_CLOSURE_ACTION_NAME') : $handler[1];

        // Execute function
        try 
        {
            $this->renderer->init(call_user_func_array($handler, $args));
        } 
        catch (\Throwable $threw)
        {
            // error handling
            //$this->renderer->errors[] = $this->locale->get('ERROR_CALLBACK_FAILED}');
            $this->renderer->init($this->error(500));
        }

     }
        

    public function run()
    {
        // Open neeeded things
        session_start();

        $route = $this->router->dispatch($this->request);
        // TODO: probably refactor to use throw Error
        if($route instanceof EmptyRoute)
        {
            $this->response = $this->error(404);
            //$this->renderer->errors[] = $this->locale->get('ERROR_NOT_FOUND');
        }
        else
        {
            $args = $route->map($this->request);
            $handler = $route->handler();
            $this->executioner($handler, $args);
        }

        // Close not needed things
        session_write_close();
        //$this->dbconnection->close();
    }

    public function render()
    {
        $this->renderer->render();
    }

    protected function getDebugProperties(): arrray 
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