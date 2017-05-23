<?php
declare(strict_types=1);

namespace Gideon\Handler;

use Countable;
use Throwable;
use ReflectionClass;
use Gideon\Exception\Any;
use Gideon\Exception\IOExcpetion;
use Gideon\Exception\Notice;
use Gideon\Exception\Warning;
use Gideon\Exception\Fatal;
use Gideon\Exception\Unknown;
use Gideon\Exception\ErrorException;
use Gideon\Http\ResponseException;
use Gideon\Config;
use Gideon\Debug\Logger;
use Psr\Log\LogLevel;

/**
 * Config keys used:
 * - LOGGER_ROOT
 * - LOGGER_LOG_TRACES
 */

final class Error implements Countable
{

    /**
     * @var string $root how deep should string with pathes in errors should be parsed
     */
    private $root;

    /**
     * @var Throwable[] $errors
     */
    private $errors;

    /**
     * @var Gideon\Debug\Logger $logger
     */
    private $logger;

    /**
     * @var bool $logTrace turn logging traces on/off
     */
    private $logTraces;

    // normalize
    protected function normalizePath(string $path): string
    {
        return is_null($this->root) ? $path : mb_substr($path, strpos($path, $this->root) + strlen($this->root));
    }

    // normalize
    protected function normalizeTraces(array $traces): array
    {
        foreach ($traces as $i => $trace) {
            if (isset($trace['file'])) {
                $traces[$i]['file'] = $this->normalizePath($trace['file']);
            }
        }
        return $traces;
    }

    public function fullErrorHandling(): self
    {
        error_reporting(E_ALL);
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                // This error code is not included in error_reporting
                return;
            }
            switch ($severity) {
                case E_STRICT:
                case E_RECOVERABLE_ERROR:
                case E_USER_ERROR:
                case E_ERROR:
                    throw new Fatal($message, 0, $severity, $file, $line);
                    break;
                case E_USER_WARNING:
                case E_WARNING:
                    throw new Warning($message, 0, $severity, $file, $line);
                    break;
                case E_USER_DEPRECATED:
                case E_DEPRECATED:
                case E_USER_NOTICE:
                case E_NOTICE:
                    throw new Notice($message, 0, $severity, $file, $line);
                    break;
                default:
                    throw new Unknown($message, 0, $severity, $file, $line);
                    break;
            }
        });
        return $this;
    }

    public function log(Throwable $err)
    {
        // Get parsed error object
        $context = $this->transform($err);

        // Prepare message
        $message = "[{{type}}:{{code}}] at: {{file}}:{{line}}\n";
        $message .= "\tMessage: {{message}}\n";

        // Optimize message for some objects
        if ($err instanceof IOExcpetion) {
            $message .= "\tPath: {{path}}";
        } elseif ($err instanceof ResponseException) {
            $message .= "\tError Code: `{{errorCode}}`";
        }

        // Add traces at the end and log it
        if (!empty($context['traces']) && $this->logTraces) {
            $trace_string = "\n\tTraces:";
            foreach ($context['traces'] as $trace) {
                $trace_string .= "\n\t- ";
                if (isset($trace['class'])) {
                    $trace_string .= "{$trace['class']}";
                }
                if (isset($trace['function'])) {
                    $type = $trace['type'] ?? '';
                    $trace_string .= "$type{$trace['function']}";
                }
                if (isset($trace['file'])) {
                    $trace_string .= "\n\t\t..at {$trace['file']}:{$trace['line']}";
                }
                if (isset($trace['args']) && !empty($trace['args'])) {
                    $args = json_encode($trace['args']);
                    $trace_string .= "\n\t\t..with $args";
                }
            }
            $message .= $trace_string;
        }

        $this->logger
            ->withPrefix('')
            ->log($context['logLevel'] ?? 'error', $message, $context);
    }

    /**
     * Transform Throwable into an array
     * @param Throwable $err
     * @param bool $normalize
     */
    public function transform(Throwable $err, bool $normalize = true): array
    {
        $transofrmed = [
            'type' => (new ReflectionClass($err))->getShortName(),
            'code' => $err->getCode(),
            'message' => $err->getMessage(),
            'line' => $err->getLine(),
            'file' => $this->normalizePath($err->getFile()),
            'traces' => $this->normalizeTraces($err->getTrace())
        ];

        if ($err instanceof Any) {
            // call getters
            foreach ($err->getGetters() as $getter) {
                $getter = trim($getter);
                $getterFunc = 'get' . strtoupper(mb_substr($getter, 0, 1)) . mb_substr($getter, 1);
                $this->logger->withPrefix('ErrorHandler')->debug("Getter name: $getterFunc");
                $transofrmed[$getter] = call_user_func([$err, $getterFunc]);
            }
        }

        return $transofrmed;
    }

    public function __construct(Config $config, Logger $logger)
    {
        $this->logger = $logger;
        $this->root = $config->get('LOGGER_ROOT');
        $this->logTraces = (bool)$config->get('LOGGER_LOG_TRACES');
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

    /**
     * Adds every
     */
    public function add(Throwable $err, bool $log = true)
    {
        do {
            $this->errors[] = $err;
            if($log) {
                $this->log($err);
                if (!($err instanceof Any)) {
                    $this->logger
                        ->withPrefix('')
                        ->info('All errors handled via ErrorHandled should be instance of Gideon\Exception\Any');
                }
            }
        } while ($err = $err->getPrevious());
    }

    public function clear(): self
    {
        $this->errors = [];
        return $this;
    }

    /**
     * @return Throwable[]
     */
    public function findAll(string $instance = null): array
    {
        if(is_null($instance))
            return $this->errors ?? [];

        $ret = [];
        foreach($this->errors as $i => $error)
        {
            if($error instanceof $instance)
                $ret[$i] = $error;
        }
        return $ret;
    }

    /**
     * @return [int, Throwable]
     */
    public function findOne(string $instance = null): ?array
    {
        if(is_null($instance))
            return [0, $this->errors[0]] ?? null;

        foreach($this->errors as $i => $error)
        {
            if($error instanceof $instance)
            {
                return [$i, $error];
            }
        }
        return null;
    }

    public function pop(int $i = null): self
    {
        unset($this->errors[$i ?? 0]);
        return $this;
    }

    /**
     * @return mixed[] array ready to encode to JSON
     */
    public function getAllTransformed(): array
    {
        $parsedErrors = [];
        foreach ($this->errors as $error) {
            $parsedErrors[] = $this->transform($error);
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
