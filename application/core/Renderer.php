<?php
namespace Gideon;

use Gideon\Debug\Base as Debug;
use Gideon\Renderer\Response;
use Gideon\Handler\Config;
use Gideon\Handler\Locale;

/**
 * Config keys used:
 * - 'RESPONSE_CODE_DEFAULT'
 * - 'RESPONSE_TYPE_DEFAULT'
 */

class Renderer extends Debug 
{

    /**
     * @var Gideon\Handler\Config       $config
     * @var Gideon\Handler\Locale       $locale
     * @var Gideon\Renderer\Response    $response
     * @var \stdClass                   $document various informations and settings that may be (or not) useful for Response object
     */
    private $config;
    private $locale;
    private $document;
    private $response;

    /**
     * Set HTTP headers
     * Remarks: Must be called before any output sent
     * @param Gideon\Renderer\Response  $response
     */
    private function setHeaders() 
    {
        // Set
        header('Content-Type: ' . $this->response->type, true, $this->response->code);

        // Verify
        if($this->response->code != http_response_code())
        {
            $this->log("Setting HTTP status response code: {$this->response->code}. Failed.");
        }
    }

    /**
     * Return final output
     */
    public function render(bool $with_headers = true)
    {
        // render HTTP headers
        if($with_headers)
            $this->setHeaders();

        // buffer and output Response
        try 
        {
            $this->response->render($this->config, $this->locale, $this->document);
        } 
        catch (\Throwable $any)
        {
            // TODO: ..
        }

        // TODO: Error handling..
    }

    public function init(Response $response)
    {
        $response->setup($this->config);

        if(!isset($response->code))
            $response->setCode($this->config->get('RESPONSE_CODE_DEFAULT'));

        if(!isset($response->type))
            $response->setType($this->config->get('RESPONSE_TYPE_DEFAULT'));

        $this->response = $response;
    }

    public function __construct(Config $config, Locale $locale)
    {
        $this->config = $config;
        $this->locale = $locale;
        $this->document = new \stdClass();
        $this->document->LANG = $locale->getLanguage();
    }

    public function __get($key) 
    {
        if(isset($this->document->{$key}))
            return $this->document->{$key};

        else $this->log("Trying to access unset variable: \$document->$key");
    }

    public function __set($key, $value) 
    {
        $this->document->{$key} = $value;
    }

    public function __isset($key): bool 
    {
        return isset($this->document->{$key}) ? true : false;
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return [
            'document' => $this->document,
            'response' => $this->response
        ];
    }

}