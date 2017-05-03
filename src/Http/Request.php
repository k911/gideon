<?php
declare(strict_types=1);

namespace Gideon\Http;

use Countable;
use IteratorAggregate;
use ArrayAccess;
use ArrayIterator;
use Traversable;
use Gideon\Exception\InvalidArgumentException;
use Gideon\Debug\Provider as Debug;
use Gideon\Handler\Config;
use Gideon\Http\Request\Params;

class Request extends Debug implements
    ArrayAccess,
    IteratorAggregate,
    Countable
{
    /**
     * @var string[] $values path values of the request
     */
    private $values;

    /**
     * @var int $size number of path values
     */
    private $size;

    /**
     * @var string uppercased http method
     */
    private $method;

    /**
     * @var Gideon\Http\Request\Params $params
     */
    private $params;

    private function parseUri(Config $config, string $uri): array
    {
        $uri = str_replace($config->get('ALIAS'), '', $uri);
        $uri = trim($uri, '/');
        $uri = filter_var($uri, FILTER_SANITIZE_URL);
        $uri = explode('/', $uri);
        $parsed = [];
        foreach ($uri as $param) {
            if (!empty($param) || $param === '0') {
                $parsed[] = $param;
            }
        }
        return $parsed;
    }

    public function __construct(Config $config, string $request = null, string $method = null, Params $params = null)
    {
        // Obtain request and method from server
        if (is_null($request)) {
            $request = $_SERVER['REQUEST_URI'];
            $method = $_SERVER['REQUEST_METHOD'];
        } // Obtain default method from config
        elseif (is_null($method)) {
            $method = $config->get('REQUEST_METHOD_DEFAULT');
        }

        // Verify if selected method is accepted by application
        $method = strtoupper($method);
        if (!in_array($method, $config->get('REQUEST_METHODS_SUPPORTED'))) {
            throw new InvalidArgumentException("Undefined/not accepted HTTP_METHOD: $method");
        }

        // Sanitize request
        $request = filter_var($request, FILTER_SANITIZE_URL);
        if(!strpos($request, '?'))
            $request .= '?';
        [$uri, $query] = explode('?', $request, 2);

        // Obtain request parameters if not given
        if (is_null($params)) {
            $params = new Params($method, $query);
        }

        $this->method = $method;
        $this->position = 0; // request uri params iterator
        $this->values = $this->parseUri($config, $uri);
        $this->size = count($this->values);
        $this->params = $params;
    }

    public function count(): int
    {
        return $this->size;
    }

    /**
     * \ArrayAccess implementation
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }
    }
    public function offsetExists($offset)
    {
        return isset($this->values[$offset]);
    }
    public function offsetUnset($offset)
    {
        unset($this->values[$offset]);
    }
    public function offsetGet($offset)
    {
        return $this->values[$offset] ?? null;
    }

    /**
     * \IteratorAgregate implementation
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->values);
    }

    /**
     * @return Gideon\Http\Request\Params
     */
    public function getParams(): Params
    {
        return $this->params;
    }

    /**
     * Get protocol name in lowercase (http or https)
     * @return string
     */
    public function getProtocol(): string
    {
        return (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') ? 'https' : 'http';
    }

    // TODO: rename to getURI()
    public function uri(): string
    {
        return implode('/', $this->values);
    }

    // TODO: rename to getHttpMethod()
    public function method(): string
    {
        return $this->method;
    }

    public function getHttpRequest(): string
    {
        $protocol = $this->getProtocol();
        $uri = $this->uri();
        $server = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
        return "{$this->method} $protocol://$uri $server";
    }

    protected function getDebugProperties(): array
    {
        return [
            'values' => $this->values,
            'method' => $this->method,
            'params' => $this->params
        ];
    }
}
