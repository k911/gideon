<?php
namespace Gideon\Controller;

use Gideon\Controller;
use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Http\Request;
use Gideon\Database\Connection;

abstract class Base implements Controller
{
    /**
     * @var Gideon\Handler\Config           $config
     * @var Gideon\Handler\Locale           $locale
     * @var Gideon\Http\Request             $request
     * @var Gideon\Http\Request\Params      $params
     * @var Gideon\Database\Connection      $connection;
     *
     * @todo @var Gideon\Http\Cookie        $cookie
     * @todo @var Gideon\Http\CSRF          $csrf
     * @todo @var Gideon\Model              $model
     */
    protected $config;
    protected $locale;
    protected $request;
    protected $params;

    public function __construct()
    {}

    public function init(Config $config, Locale $locale, Request $request, Connection $connection)
    {
        $this->config = $config;
        $this->locale = $locale;
        $this->request = $request;
        $this->connection = $connection;
        $this->params = new Request\Params($request->method());
    }
    

}