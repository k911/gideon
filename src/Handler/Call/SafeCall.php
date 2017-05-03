<?php
declare(strict_types=1);

namespace Gideon\Handler\Call;

use Throwable;
use Gideon\Handler\Call;
use Gideon\Handler\Error as ErrorHandler;

class SafeCall implements Call
{

    /**
     * @var callable $expression
     */
    private $handler;

    /**
     * @var callable $expression
     */
    protected $expression;

    /**
     * @var array $arguments
     */
    protected $arguments;

    /**
     * @var callable $callback
     */
    protected $callback;

    /**
     * @var callable $onErrorCallback
     */
    protected $onErrorCallback;

    /**
     * @var array $onErrorArguments
     */
    protected $onErrorArguments;

    /**
     * @param ErrorHandler $handler
     * @param callable $expression expression to call
     * @param array $arguments arguments of expression to call
     * @param callable $callback expression to call when $expression finishes
     */
    public function __construct(ErrorHandler $handler, callable $expression = null, array $arguments = null, callable $callback = null)
    {
        $this->handler = $handler;
        $this->expression = $expression;
        $this->callback = $callback;
        $this->verifyAndSetArguments($arguments);
    }

    /**
     * Used to make this object as callable to make possible nesting it
     * via setCallback(SafeCall)
     */
    public function __invoke(...$args)
    {
        if (!empty($args)) {
            $this->arguments = $args;
        }
        return $this->call();
    }

    public function call()
    {
        $result = null;
        try {
            $result = is_null($this->arguments) ?
                call_user_func($this->expression) :
                call_user_func_array($this->expression, $this->arguments);
        } catch (Throwable $err) {
            $this->handler->add($err);
        } finally {
            // Fire callback if possible
            if ($this->handler->isEmpty()) {
                if (isset($this->callback)) {
                    $result = $this->setExpression($this->callback)
                        ->setArguments($result)
                        ->setCallback(null)
                        ->call();
                }
            // Fire errror callback if set
            } elseif (isset($this->onErrorCallback)) {
                $result = $this->setExpression($this->onErrorCallback, $this->onErrorArguments)
                    ->setCallback(null)
                    ->onError(null)
                    ->call();
            }
        }
        return $result;
    }

    public function setExpression(callable $expression, array $arguments = null): Call
    {
        $this->expression = $expression;
        return $this->verifyAndSetArguments($arguments);
    }

    public function setCallback(callable $callback = null): Call
    {
        $this->callback = $callback;
        return $this;
    }

    public function onError(callable $callback = null, ...$arguments): Call
    {
        $this->onErrorCallback = $callback;
        $this->onErrorArguments = $arguments;
        return $this;
    }

    /**
     * Helper function to set arguments
     */
    protected function verifyAndSetArguments(array $arguments = null): Call
    {
        $this->arguments = empty($arguments) ? null : $arguments;
        return $this;
    }

    public function setArguments(...$arguments): Call
    {
        return $this->verifyAndSetArguments($arguments);
    }
}
