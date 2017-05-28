<?php
namespace Gideon\Http\Response;

use stdClass;
use Gideon\Config;
use Gideon\Locale;
use Gideon\Http\Response;

/**
 * Config keys used:
 * - 'VIEW_TYPE_DEFAULT'
 * - 'VIEW_PATH'
 * - 'VIEW_DEFAULT'
 * - 'VIEW_HEADER'
 * - 'VIEW_FOOTER'
 */
class View extends Base
{

    public function setHandler($view): Response
    {
        $view = (string)$view;

        $this->handler = new stdClass();
        $this->handler->name = $view;
        $this->handler->file = "$view.view.php";
        return $this;
    }

    public function setup(Config $config)
    {
        if(is_null($this->type))
            $this->type = $config->get('VIEW_TYPE_DEFAULT');

        if(!file_exists($this->handler->file = $config->get('VIEW_PATH') . $this->handler->file))
        {
            $this->getLogger()->warning("View '{$this->handler->name}' doesn't exists. Setting default.");
            $this->handler->name = $config->get('VIEW_DEFAULT');
            $this->handler->file = $this->handler->name . '.php';
        }

        $this->handler->header = $config->get('VIEW_HEADER');
        $this->handler->footer = $config->get('VIEW_FOOTER');
    }

    public function render(Config $config, Locale $locale = null, stdClass $document = null)
    {
        // make its own scope for 'pretty' variables in files
        call_user_func(function(Config $config, Locale $locale, stdClass $params, stdClass $view, stdCLass $document)
        {
            require $view->header;
            flush(); // https://www.sitepoint.com/faster-web-pages-php-buffer-flush/
            require $view->file;
            require $view->footer;
        },
        $config, $locale, $this->params, $this->handler, $document);
    }

}
