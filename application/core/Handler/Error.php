<?php
declare(strict_types=1);

namespace Gideon\Handler;

use Countable;
use Throwable;
use Gideon\Handler\Config;
use Gideon\Debug\Logger;
use Psr\Log\LogLevel;

class Error implements Countable
{

    /**
     * @var string $root how deep should string with pathes in errors should be parsed
     */
    private $root;

    /**
     * @var Throwable[] $errors
     */
    private $errors;


    protected function parsePath(string $path): string
    {
        return is_null($this->root) ? $path : substr($path, strpos($path, $this->root) + strlen($this->root));
    }

    protected function parseTraces(array $traces): array
    {
        foreach ($traces as $index => $trace) {
            if (isset($trace['file'])) {
                $traces[$index]['file'] = $this->parsePath($trace['file']);
            }
        }
        return $traces;
    }

    public function parseError(Throwable $err): array
    {
        return [
            'type' => get_class($err),
            'code' => $err->getCode(),
            'message' => $err->getMessage(),
            'line' => $err->getLine(),
            'file' => $this->parsePath($err->getFile()),
            'traces' => $this->parseTraces($err->getTrace())
        ];
    }

    // TODO: Require logger
    public function __construct(string $root = null, Logger $logger = null)
    {
        $this->root = $root;
        $this->logger = $logger;
    }

    public function has(string $err_instance)
    {
        foreach ($this->errors as $err) {
            if ($err instanceof $err_instance) {
                return true;
            }
        }
        return false;
    }

    public function add(Throwable $err)
    {
        do {
            $this->errors[] = $err;
            if(isset($this->logger))
                $this->logger->error($err);
        } while ($err = $err->getPrevious());
    }

    public function clean()
    {
        $this->errors = [];
    }

    /**
     * @param callable $callback
     * @param array $arguments
     * @return mixed
     */
    public function handle(callable $callback, ...$arguments)
    {
        $result = null;
        try {
            $result = (isset($arguments)) ?
                call_user_func_array($callback,
                    (count($arguments) == 1 && is_array($arguments[0])) ? $arguments[0] : $arguments) :
                call_user_func($callback);
        } catch (Throwable $err) {
            $this->add($err);
        }
        finally
        {
            return $result;
        }
    }

    public function getAll(): array
    {
        return $this->errors;
    }

    public function getFirst(): Throwable
    {
        return $this->errors[0];
    }

    /**
     * @return mixed[] array ready to encode to JSON
     */
    public function getAllParsed(): array
    {
        $parsedErrors = [];
        foreach ($this->errors as $error) {
            $parsedErrors[] = $this->parseError($error);
        }
        return $parsedErrors;
    }

    public function count()
    {
        return count($this->errors);
    }

    public function isEmpty(): bool
    {
        return empty($this->errors);
    }
}
