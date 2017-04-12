<?php

namespace Gideon;

use Gideon\Debug\Provider as Debug;
use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Http\Request;
use Gideon\Http\Response;
use Gideon\Exception\InvalidArgumentException;
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
    /**
     * @var \Gideon\Database\Connection $connection
     */
    protected $connection;

    /**
     * @var \Gideon\Handler\Config $config
     */
    protected $config;

    /**
     * @var \Gideon\Handler\Locale $locale
     */
    protected $locale;

    /**
     * @var \Gideon\Router $router
     */
    protected $router;

    /**
     * @var \Gideon\Http\Request $request
     */
    protected $request;

    /**
     * @var \Gideon\Renderer $renderer
     */
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

    protected function exec(callable $handler, array $arguments = null)
    {
        if ($handler instanceof \Closure) {
            $handler = [(new Controller\Anonymous())->setCallback($handler), 'callback'];
        }

        if (!is_array($handler) || !($handler[0] instanceof Controller)) {
            throw new InvalidArgumentException('Given object is not a valid Controller');
        }

        [$controller, $action] = $handler;
        $controller->initController($this->config, $this->locale, $this->request, $this->connection);
        $name = str_replace($this->config->get('APPLICATION_CONTROLLER_PREFIX'), '', get_class($controller));

        $this->renderer->controller = $name;
        $this->renderer->action = $action;
        $this->renderer->initResponse($controller->callAction($action, $arguments));
    }

    public function run()
    {
        // Open neeeded things
        session_start();

        $handler = new ErrorHandler($this->config->get('LOGGER_ROOT'));
        $handler->handle(function ($app) {
            // Dispatch
            $route = $app->router->dispatch($this->request);

            // Execute MVC
            if ($route instanceof EmptyRoute) {
                $app->exec([new Controller\Error(), 'NotFound'], [$route]);
            } else {
                $app->exec($route->callback(), $route->map($app->request));
            }
        }, $this);

        // Log not resolved throwables
        if (!$handler->isEmpty()) {
            foreach ($handler->getAll() as $err) {
                $this->logger()->error($err);
            }
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
