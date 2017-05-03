<?php
declare(strict_types=1);

namespace Gideon\Handler;

interface Call {

    /**
     * Set / override expression arguments
     * @param mixed[] $arguments
     * @return Gideon\Handler\Call
     */
    public function setArguments(...$arguments): self;

    /**
     * Call expression with its arguments and callback
     * @return mixed
     */
    public function call();

    /**
     * Sets expression call with its arguments
     * @param callable $expression
     * @param mixed[] $arguments
     * @return Gideon\Handler\Call
     */
    public function setExpression(callable $expression, array $arguments = null): self;

    /**
     * Sets function that should be called after successful expression call
     * Callback will be called with first argument as result of called expression
     * @param callable $callback
     * @return Gideon\Handler\Call
     */
    public function setCallback(callable $callback): self;

    /**
     * Sets function that should be called after unsuccessful expression call
     * @param callable $callback
     * @return Gideon\Handler\Call
     */
    public function onError(callable $callback): self;
}
