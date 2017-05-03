<?php

namespace Gideon;

use ReflectionClass;
use Gideon\Exception\InvalidArgumentException;
use Gideon\Application\SystemFailure;
use Gideon\Debug\Provider as Debug;
use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Handler\Call\SafeCall;
use Gideon\Http\Request;
use Gideon\Http\Response;
use Gideon\Database\Connection;
use Gideon\Http\NotFoundException;
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

    /**
     * @var \Gideon\Handler\Error $errorHandler
     */
    protected $errorHandler;

    /**
     * @var Gideon\Controller\Error $errorController
     */
    private $errorController;

    public function __construct(Config $config, Router $router)
    {
        $locale = new Locale($config);
        $request = new Request($config);
        $errorHandler = new ErrorHandler($config, $config->getLogger());
        $errorHandler->fullErrorHandling();
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
        $this->errorHandler = $errorHandler;
    }

    protected function prepare(callable $handler): callable
    {
        // Construct controller for \Closure and normal functions
        if ($handler instanceof \Closure || is_string($handler)) {
            $handler = [(new Controller\Anonymous())->setCallback($handler), 'callback'];
        }

        // Verify
        if (!($handler[0] instanceof Controller)) {
            throw new InvalidArgumentException('Given object is not a valid Controller');
        }
        if (!method_exists($handler[0], $handler[1])) {
            $controller = get_class($handler[0]);
            throw new InvalidArgumentException("Controller $controller does NOT have action {$handler[1]}");
        }

        // Init Controller
        $handler[0]->init($this->errorHandler, $this->config, $this->locale, $this->request, $this->connection);
        return $handler;
    }

    /**
     * Create and initialize error controller object
     * @return void
     */
    private function createErrorController()
    {
        // TODO: use Config('ERROR_CONTROLLER')
        $this->errorController = new Controller\Error($this->errorHandler, $this->config, $this->locale, $this->request, $this->connection);
    }

    /**
     * Get initialized error controller object
     * @return Gideon\Controller
     */
    protected function getErrorController(): Controller
    {
        if (!isset($this->errorController)) {
            $this->createErrorController();
        }
        return $this->errorController;
    }

    public function getErrorCallback(string $action): callable
    {
        // TODO: use Config('ERROR_ACTION_STAGE_ROUTING')
        // Exceptions: ERROR_ACTION_STAGE_EXECUTING, ?ERROR_ACTION_STAGE_VALIDATING_REQUEST,
        // Helper actions for debug (ERRORS): ERROR_ACTION_VALIDATE_OUTPUT, ERROR_ACTION_HANDLE_FAILURE
        $controller = $this->getErrorController();
        if (!method_exists($controller, $action)) {
            $class = get_class($controller);
                throw new SystemFailure("Error controller `$class` doesn't have action `$action`.");
        }
        return [$controller, $action];
    }

    public function run()
    {
        // Open neeeded things
        session_start();

        $handler = $this->errorHandler;

        // Catch errors during routing and request validation
        [$controller, $action, $arguments] = (new SafeCall($handler, function($app) {
            // Dispatch request
            $route = $app->router->dispatch($app->request);
            if ($route instanceof EmptyRoute) {
                throw new NotFoundException('[Not Found] '. $app->request->getHttpRequest());
            }

            // Parse route data
            $callback = $route->getCallback();
            [$controller, $action] = $app->prepare($callback);
            $arguments = $route->map($app->request);
            return [$controller, $action, $arguments];
        }))->setArguments($this)
            // Catch errors during routing
            ->onError(function($app) {
                [$controller, $action] = $app->getErrorCallback('router');
                return [$controller, $action, [$app->errorHandler]];
            }, $this)
            ->call();

        // Catch errors during getting response from MVC
        $response = (new SafeCall($handler, [$controller, $action], $arguments))
            ->onError($this->getErrorCallback('controller'), $handler, $controller, $action)
            ->call();

        // Last line of defence if defined error handler fails..
        if (!$handler->isEmpty()) {
            $response = (new SafeCall($handler, $this->getErrorCallback('unhandled')))
                ->setArguments($handler, $response)
                ->onError($this->getErrorCallback('failure'), $handler)
                ->call();
        }

        // Store some informations
        $this->renderer->controller = (new ReflectionClass($controller))->getShortName();
        $this->renderer->action = $action;
        $this->renderer->attach($response);

        // Close not needed things
        $this->connection->close();
        session_write_close();
    }

    public function render()
    {
        $this->renderer->render($this->errorHandler);
        if(!$this->errorHandler->isEmpty())
        {
            echo 'redirect then render errors';
        }
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
