<?php
namespace Gideon\Renderer\Response;

use Gideon\Handler\Config;
use Gideon\Handler\Locale;
use Gideon\Renderer\Response;

/**
 * Config keys used:
 * - 'JSON_TYPE_DEFAULT'
 * - 'JSON_TYPES_SUPPORTED'
 */
class JSON extends Base
{
    public function setHandler($data): Response
    {
        if(!is_array($data) && !($data instanceof \stdClass))
            $data = [$data];

        $this->handler = $data;
        return $this;
    }

    public function setup(Config $config)
    {
        if(is_null($this->type) || in_array($this->type, $config->get('JSON_TYPES_SUPPORTED')))
            $this->type = $config->get('JSON_TYPE_DEFAULT');
    }

    public function render(Config $config, Locale $locale, \stdClass $document)
    {
        $result = [$config->get('JSON_CONTAINER_RESULT') => $this->handler];

        // Include document object
        if($this->params->includeDocument)
        {
            $result['document'] = $document;
        }

        // Handle errors object
        if(!empty($this->params->errors))
        {
            $result['errors'] = $this->params->errors;
            if(!$this->params->includeDocument && $this->params->includeDocumentOnError)
            {
                $result['document'] = $document;
            }
        }

        // Print to buffer
        echo json_encode($result);
    }

    public function __construct($data = null, int $code = null, string $type = 'text/json')
    {
        parent::__construct($data, $code, $type);

        // Used params in rendering
        $this->params->includeDocument = false;
        $this->params->includeDocumentOnError = true;
        $this->params->errors = [];
    }
}