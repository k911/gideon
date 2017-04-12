<?php
namespace Gideon\Controller;

use Throwable;
use Gideon\Exception\InvalidArgumentException;
use Gideon\Controller;
use Gideon\Handler\Config;
use Gideon\Handler\Locale;
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
     * @var \Gideon\Handler\Config $config
     */
    protected $config;

    /**
     * @var \Gideon\Handler\Locale $locale
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
     * @var \Gideon\Debug\Logger $logger
     */
    protected $logger;

    public function __construct()
    {}

    public function initController(Config $config, Locale $locale, Request $request, Connection $connection)
    {
        $this->config = $config;
        $this->locale = $locale;
        $this->request = $request;
        $this->params = $request->getParams();
        $this->connection = $connection;
        $this->logger = $config->logger()->withPrefix(get_class($this)); // TODO: fix double logger prefix change
    }

    /**
     * Overridable
     */
    public function callAction(ErrorHandler $handler, string $action, array $arguments = null): Response
    {
        return $handler->handle([$this, $action], $arguments);
    }
}
