<?php
declare(strict_types=1);

namespace Gideon\Http\Request;

use Countable;
use Gideon\Debug\Provider as Debug;

/**
 * Class handling request's query parameters of every HTTP method
 * But only when its content-type is: application/x-www-form-urlencoded
 */
class Params extends Debug implements Countable
{
    /**
     * @var string[] $data parsed query parameters
     */
    private $data;

    /**
     * @param string $method http method
     * @param string $query parameters
     */
    public function __construct(string $method, string $query = null)
    {
        $method = strtoupper($method);

        if (!empty($query)) {
            $query = trim($query, "?\s\t\n");
            parse_str($query, $this->data);
        } elseif ($method === 'POST' || $method === 'GET') {
            $this->data = $GLOBALS["_$method"];
        } else {
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
        if (isset($this->data->{$key})) {
            return $this->data->{$key};
        }
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function getAll(): array
    {
        return $this->data;
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return ['data' => $this->data];
    }
}
