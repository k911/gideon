<?php
declare(strict_types=1);

namespace Gideon\Controller;

use Gideon\Exception\InvalidArgumentException;
use Throwable;
use Gideon\Debug\Logger;
use Gideon\Controller;
use Gideon\Controller\Error as ErrorController;
use Gideon\Handler\Call\SafeCall;
use Gideon\Config;
use Gideon\Locale;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Http\Request;
use Gideon\Http\Response;
use Gideon\Database\Connection;

abstract class Base implements Controller
{
    /**
     * @todo @var Gideon\Http\Cookie        $cookie
     * @todo @var Gideon\Http\CSRF          $csrf
     * @todo @var Gideon\Model              $model
     */

    /**
     * @var \Gideon\Config $config
     */
    protected $config;

    /**
     * @var \Gideon\Locale $locale
     */
    protected $locale;

    /**
     * @var \Gideon\Http\Request $request
     */
    protected $request;

    /**
     * @var \Gideon\Http\Request\Params $params
     */
    protected $params;

    /**
     * @var \Gideon\Database\Connection $connection;
     */
    protected $connection;

    /**
     * @var \Gideon\Handler\Error $errorHandler;
     */
    protected $errorHandler;

    /**
     * @var \Gideon\Debug\Logger $logger
     */
    private $logger;

    public function getLogger(): Logger
    {
        return $this->logger->withPrefix(get_class($this));
    }

    public function __construct(ErrorHandler $errorHandler = null, Config $config = null, Locale $locale = null, Request $request = null, Connection $connection = null)
    {
        $this->init($errorHandler, $config, $locale, $request, $connection);
    }

    public function init(ErrorHandler $errorHandler = null, Config $config = null, Locale $locale = null, Request $request = null, Connection $connection = null)
    {
        $params = isset($request) ? $request->getParams() : null;
        $logger = isset($config) ? $config->getLogger() : null;

        $this->errorHandler = $errorHandler;
        $this->config = $config;
        $this->locale = $locale;
        $this->request = $request;
        $this->params = $params;
        $this->connection = $connection;
        $this->logger = $logger;
    }
}
