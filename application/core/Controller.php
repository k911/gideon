<?php
namespace Gideon;

use Throwable;
use Gideon\Http\Response;
use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Http\Request;
use Gideon\Database\Connection;

/**
 * Controller should be as light as possible
 */
interface Controller 
{
    /**
     * Require no constructor parameters
     */
    public function __construct();

    /**
     * Init with needed things
     * @param \Gideon\Handler\Config         $config
     * @param \Gideon\Handler\Locale         $locale
     * @param \Gideon\Http\Request           $request
     * @param \Gideon\Database\Connection    $connection
     */
    public function initController(Config $config, Locale $locale, Request $request, Connection $connection);

    /**
     * Calls action of controller
     * @param string $action
     * @param array $arguments
     * @return Response
     */
    public function callAction(string $action, ...$arguments): Response;

    /**
     * Error Handler
     * @param ErrorHandler $error
     * @return Response
     */
    public function handleErrors(ErrorHandler $handler): Response;

}