<?php
namespace Gideon\Http\Response;

use stdClass;
use Gideon\Debug\Provider as Debug;
use Gideon\Http\Response;
use Gideon\Config;
use Gideon\Locale;

abstract class Base extends Debug implements Response
{
    /**
     * @var mixed $handler a unknown type data (e.g. filename)
     */
    protected $handler;

    /**
     * @var string $type http content-type value
     */
    protected $type;

    /**
     * @var int $code http response code
     */
    protected $code;

    /**
     * Array of headers
     * @var array
     */
    protected $headers;

    /**
     * @var iterable $params variables used in rendering process
     */
    protected $params;

    public function setHandler($handler): Response
    {
        $this->handler = $handler;
        return $this;
    }
    public function setCode(int $code): Response
    {
        $this->code = $code;
        return $this;
    }
    public function setType(string $type): Response
    {
        $this->type = $type;
        return $this;
    }
    public function setHeader(string $name, string $value): Response
    {
        if (!empty($name) && !empty($value)) {
            $this->headers[$name] = $value;
            if($name === 'Content-Type') {
                $this->getLogger()->warning('Content-Type header MUST be set via setType() method.');
            }
        }
        return $this;
    }

    public function bindParam(string $param, &$value): Response
    {
        $this->params->{$param} = &$value;
        return $this;
    }

    public function setParam(string $param, $value): Response
    {
        $this->params->{$param} = $value;
        return $this;
    }

    public function setParams(array $params): Response
    {
        foreach ($params as $key => $value) {
            $this->setParam($key, $value);
        }
        return $this;
    }

    public function __construct($handler = null, int $code = null, string $type = null)
    {
        $this->params = new stdClass();

        if (isset($handler)) {
            $this->setHandler($handler);
        }
        if (isset($code)) {
            $this->setCode($code);
        }
        if (isset($type)) {
            $this->setType($type);
        }
    }

    public function __get(string $key)
    {
        if (isset($this->{$key})) {
            return $this->$key;
        }
    }

    public function __isset(string $key): bool
    {
        return isset($this->{$key});
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type,
            'handler' => $this->handler,
            'params' => $this->params
        ];
    }
}
