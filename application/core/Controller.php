<?php
namespace Gideon;

use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Http\Request;
use Gideon\Database\Connection;

/**
 * Controller should be as light as possible
 */
interface Controller 
{
    /**
     * No constructor params
     */
    public function __construct();

    /**
     * Init with needed things
     * @param Gideon\Handler\Config         $config
     * @param Gideon\Handler\Locale         $locale
     * @param Gideon\Http\Request           $request
     * @param Gideon\Database\Connection    $connection
     */
    public function init(Config $config, Locale $locale, Request $request, Connection $connection);
}