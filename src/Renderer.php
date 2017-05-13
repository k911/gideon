<?php
namespace Gideon;

use stdClass;
use Throwable;
use Gideon\Debug\Provider as Debug;
use Gideon\Http\Response;
use Gideon\Application\Config;
use Gideon\Application\Locale;
use Gideon\Handler\Call\SafeCall;
use Gideon\Handler\Error as ErrorHandler;

/**
 * Config keys used:
 * - 'RESPONSE_CODE_DEFAULT'
 * - 'RESPONSE_TYPE_DEFAULT'
 */

class Renderer extends Debug
{
    /**
     * @var \Gideon\Application\Config $config
     */
    private $config;

    /**
     * @var \Gideon\Application\Locale $locale
     */
    private $locale;

    /**
     * @var stdClass $document various informations and settings that may be (or not) useful for Response object
     */
    private $document;

    /**
     * @var \Gideon\Http\Response $response
     */
    private $response;

    /**
     * Set HTTP headers
     * Remarks: Must be called before any output sent
     * @param \Gideon\Http\Response $response
     */
    private function setHeaders()
    {
        // Set
        header('Content-Type: ' . $this->response->type, true, $this->response->code);

        // TODO: Set additional headers from $this->response->headers

        // Verify
        if ($this->response->code != http_response_code()) {
            $this->getLogger()->error("Setting HTTP status response code: {$this->response->code}. Failed.");
        }
    }

    /**
     * Return final output
     */
    public function render(ErrorHandler $handler, bool $with_headers = true)
    {
        // render HTTP headers
        if ($with_headers) {
            $this->setHeaders();
        }

        // buffer and output Response
        $buffer = (new SafeCall($handler, [$this->response, 'render']))
            ->setArguments($this->config, $this->locale, $this->document)
            ->call();
    }

    /**
     * @param Gideon\Http\Response $response
     */
    public function attach(Response $response)
    {
        $response->setup($this->config);

        if (!isset($response->code)) {
            $response->setCode($this->config->get('RESPONSE_CODE_DEFAULT'));
        }

        if (!isset($response->type)) {
            $response->setType($this->config->get('RESPONSE_TYPE_DEFAULT'));
        }

        $this->response = $response;
        return $this;
    }

    public function __construct(Config $config, Locale $locale = null)
    {
        $this->config = $config;
        $this->locale = $locale;
        $this->document = new stdClass();

        if(isset($locale))
            $this->document->LANG = $locale->getLocale();
    }

    public function __get($key)
    {
        if (isset($this->document->{$key})) {
            return $this->document->{$key};
        } else {
            $this->getLogger()->warning("Trying to access unset variable: \$document->$key");
        }
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
