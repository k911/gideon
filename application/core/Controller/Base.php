<?php
namespace Gideon\Controller;

use Gideon\Controller;
use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Http\Request;

abstract class Base implements Controller
{
    /**
     * @var Gideon\Handler\Config           $config
     * @var Gideon\Handler\Locale           $locale
     * @var Gideon\Http\Request             $request
     * @var Gideon\Http\Request\Params      $params
     *
     * @todo @var Gideon\DBConnection       $db;
     * @todo @var Gideon\Http\Cookie        $cookie
     * @todo @var Gideon\Http\CSRF          $csrf
     * @todo @var Gideon\Model              $model
     */
    protected $config;
    protected $locale;
    protected $request;
    protected $params;

    public function __construct(Config $config, Locale $locale, Request $request)
    {
        $this->config = $config;
        $this->locale = $locale;
        $this->request = $request;
        $this->params = new Request\Params($request->method());
    }
    

}