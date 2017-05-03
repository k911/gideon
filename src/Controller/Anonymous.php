<?php
namespace Gideon\Controller;

use Closure;
use Gideon\Controller;
use Gideon\Http\Response;

/**
 * Controller for annonymous functions given in Route
 */
final class Anonymous extends Base
{
    /**
     * @var Closure $callback
     */
    private $callback;

    public function callback(...$arguments): Response
    {
        $arguments[] = $this->config;
        $arguments[] = $this->locale;
        $arguments[] = $this->request;
        $arguments[] = $this->connection;
        return call_user_func_array($this->callback, $arguments);
    }

    public function setCallback(callable $callback): Controller
    {
        $this->callback = $callback;
        return $this;
    }
}
