<?php
namespace Gideon\Controller;

use Closure;
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
        $arguments['controller'] = $this;
        return call_user_func_array($this->closure, $arguments);
    }

    public function setCallback(Closure $anon)
    {
        $this->callback = $anon;
    }
}