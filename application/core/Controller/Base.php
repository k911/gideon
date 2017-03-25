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
     * @var \Gideon\Database\Connection      $connection;
     */
    protected $connection;

    public function init(Config $config, Locale $locale, Request $request, Connection $connection)
    {
        $this->config = $config;
        $this->locale = $locale;
        $this->request = $request;
        $this->connection = $connection;
        
        $this->params = new Request\Params($request->method());
    }
    

}