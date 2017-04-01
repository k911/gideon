<?php
namespace Gideon\Http;

use Gideon\Debug\Provider as Debug;
use Gideon\Handler\Config;

class Request extends Debug implements 
    \ArrayAccess, 
    \IteratorAggregate,
    \Countable
{
    /**
     * @var int                         $position position in current iteration
     * @var string[]                    $values of the request
     * @var int                         $size of the $values
     * @var string                      $method uppercased HTTP_METHOD
     */
    private $values;
    private $size;
    private $method;

    private function parseUri(Config $config, string $uri): array
    {
        $uri = str_replace($config->get('ALIAS'), '', $uri);
        $uri = trim($uri, '/');
        $uri = filter_var($uri, FILTER_SANITIZE_URL);
        $uri = explode('/', $uri);
        $parsed = [];
        foreach($uri as $param)
        {
            if(!empty($param) || $param === '0')
                $parsed[] = $param;
        }
        return $parsed;
    }

    public function __construct(Config $config, string $request = null, string $method = null) 
    {
        // Obtain request and method from server
        if(is_null($request))
        {
            $request = $_SERVER['REQUEST_URI'];
            $method = $_SERVER['REQUEST_METHOD'];
        }

        // Obtain default method from config
        elseif(is_null($method))
            $method = $config->get('REQUEST_METHOD_DEFAULT');

        // Verify if selected method is accepted by application
        if(!in_array($method, $config->get('REQUEST_METHODS_SUPPORTED')))
            throw new InvalidArgumentException("Undefined/not accepted HTTP_METHOD: $method");
        
        $this->method = $method;
        $this->position = 0; // request uri params iterator
        $this->values = $this->parseUri($config, $request);
        $this->size = count($this->values);
    }

    public function count(): int
    {
        return $this->size;
    }

    public function uri(): string
    {
        return implode('/', $this->values);
    }

    public function method(): string
    {
        return $this->method;
    }

    /**
     * \ArrayAccess implementation
     */
    public function offsetSet($offset, $value) 
    {
        if (is_null($offset)) 
        {
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
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->values);
    }

    protected function getDebugProperties(): array
    {
        return [
            'values' => $this->values,
            'method' => $this->method
        ];
    }
}