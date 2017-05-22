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
class JSON extends Base
{

    public function setHandler($data): Response
    {
        if(!is_array($data) && !($data instanceof stdClass))
            $data = [$data];

        $this->handler = $data;
        return $this;
    }

    public function bindParam(string $param, $value): Response
    {
        $this->params[$param] = $value;
        return $this;
    }

    public function setup(Config $config)
    {
        if(is_null($this->type) || in_array($this->type, $config->get('JSON_TYPES_SUPPORTED')))
            $this->type = $config->get('JSON_TYPE_DEFAULT');
    }

    public function render(Config $config, Locale $locale = null, stdClass $document = null)
    {
        // Print to buffer
        echo json_encode(array_merge($this->handler, $this->params));
    }

    public function __construct($data = null, int $code = null, string $type = null)
    {
        $this->params = [];

        if(isset($data))
            $this->setHandler($data);
        if(isset($code))
            $this->setCode($code);
        $this->setType($type ?? 'text/json');
    }
}
