<?php
namespace Gideon\Http\Response;

use stdClass;
use Gideon\Config;
use Gideon\Locale;
use Gideon\Http\Response;

/**
 * Config keys used:
 * - 'JSON_TYPE_DEFAULT'
 * - 'JSON_TYPES_SUPPORTED'
 */
class Redirect extends Base
{

    public function setHandler($uri): Response
    {
        $this->handler = $uri;
        return $this;
    }

    public function setup(Config $config)
    {
        if(is_null($this->code))
            $this->code = 307;

        if(is_null($this->type))
            $this->type = $config->get('RESPONSE_TYPE_DEFAULT');

        $url = $this->handler;
        $url = ($this->params->LOCAL ? $config->get('URL') : '') . $url;
        $this->setHeader('Location', $url);
    }

    public function render(Config $config, Locale $locale = null, stdClass $document = null)
    {
        header('Connection: close');
        exit();
    }

    public function __construct($url = null, int $code = null, string $type = null)
    {
        parent::__construct($url, $code, $type);
        $this->params->LOCAL = false;
    }
}
