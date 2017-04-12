<?php

namespace Gideon;

use ReflectionClass;
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

    protected function prepare(callable $handler): callable
    {
        // Construct controller for closures
        if ($handler instanceof \Closure) {
            $handler = [(new Controller\Anonymous())->setCallback($handler), 'callback'];
        }

        // Verify
        if (!is_array($handler) || !($handler[0] instanceof Controller)) {
            throw new InvalidArgumentException('Given object is not a valid Controller');
        }
        if (!method_exists($handler[0], $handler[1])) {
            $controller = get_class($handler[0]);
            throw new InvalidArgumentException("Controller $controller does NOT have action {$handler[1]}");
        }

        // Init Controller
        $handler[0]->initController($this->config, $this->locale, $this->request, $this->connection);
        return $handler;
    }

    public function run()
    {
        // Open neeeded things
        session_start();

        $handler = new ErrorHandler($this->config->get('LOGGER_ROOT'));

        // Catch dispatching errors
        [$callback, $arguments] = $handler->handle(function ($app) {
            $route = $app->router->dispatch($this->request);
            $callback = $route->callback();
            $arguments = $route->map($app->request);
            return [$callback, $arguments];
        }, $this);

        // Handle Routing Errors
        if (!$handler->isEmpty()) {
            $callback = [new Controller\Error(), 'innerStage'];
            $arguments = [$handler];
        }

        // Delegate request to proper controller
        $response = $handler->handle(function ($app, $handler, $callback, $arguments) {
            [$controller, $action] = $app->prepare($callback);
            return $controller->callAction($handler, $action, $arguments);
        }, $this, $handler, $callback, $arguments);

        // Proper Error Handler
        if(!$handler->isEmpty()) {
            $callback = [new Controller\Error(), 'outerStage'];
            $response = $handler->handle(function ($app, $handler, $callback) {
                [$controller, $action] = $app->prepare($callback);
                return $controller->callAction($handler, $action, [$handler]);
            }, $this, $handler, $callback);
        }

        // Last line of defence if error handler fails.. NOT SAFE but very practical
        if (!isset($response) && !$handler->isEmpty()) {
            var_dump($handler->getAll());
            exit(1);
        }

        // Store some informations
        [$controller, $action] = $callback;
        $this->renderer->controller = (new ReflectionClass($controller))->getShortName();
        $this->renderer->action = $action;
        $this->renderer->initResponse($response);

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
