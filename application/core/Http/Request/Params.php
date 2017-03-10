<?php

namespace Gideon\Http\Request;

use Gideon\Debug\Base as Debug;

/**
 * Class handling request's parameters of every HTTP method
 * But only when its content-type is: application/x-www-form-urlencoded
 */
class Params extends Debug
{
    private $data;
    
    /**
     * @param string $method uppercased HTTP_METHOD
     */
    public function __construct(string $method)
    {
        if($method === 'POST' || $method === 'GET')
        {
            $this->data = $GLOBALS["_$method"];
        }
        else 
        {
            parse_str(file_get_contents('php://input'), $this->data);
        }
    }


    /**
     * @param string $key
     * @return bool
     */
    public function __isset($key): bool
    {
        return isset($this->data->{$key});
    }

    /**
     * @param string $key
     * @return mixed value of asked key or null
     */
    public function __get($key)
    {
        if(isset($this->data->{$key}))
            return $this->data->{$key};
    }

    public function size(): int
    {
        return count($this->data);
    }


    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return ['data' => $this->data];
    }
}