<?php
namespace Gideon;

use Gideon\Http\Response;
use Gideon\Application\Config;
use Gideon\Application\Locale;
use Gideon\Http\Request;
use Gideon\Database\Connection;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Debug\Logger;

/**
 * Controller
 */
interface Controller
{
    /**
     * @param \Gideon\Handler\Error $handler
     * @param \Gideon\Application\Config $config
     * @param \Gideon\Application\Locale $locale
     * @param \Gideon\Http\Request $request
     * @param \Gideon\Database\Connection $connection
     */
    public function __construct(ErrorHandler $handler = null, Config $config = null, Locale $locale = null, Request $request = null, Connection $connection = null);

    /**
     * Late constructor
     * @param \Gideon\Handler\Error $handler
     * @param \Gideon\Application\Config $config
     * @param \Gideon\Application\Locale $locale
     * @param \Gideon\Http\Request $request
     * @param \Gideon\Database\Connection $connection
     */
    public function init(ErrorHandler $handler = null, Config $config = null, Locale $locale = null, Request $request = null, Connection $connection = null);

    /**
     * @return Logger intialized for controller logger instance
     */
    public function getLogger(): Logger;
}
