<?php
namespace Gideon\Router;

use Countable;
use Gideon\Http\Request;

interface Route extends Countable
{
    /**
     * Maps volatile parameters with request values
     * @param \Gideon\Http\Request $request
     * @return string[] values
     */
    public function map(Request $request): array;

    /**
     * Checks if route has no parameters
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Replaces parameters which names are in replacements array
     * @param array $replacements
     * @return \Gideon\Rotuer\Route
     */
    public function where(array $replacements): self;

    /**
     * Produces regex string, which should be matched by compatible request
     * @param array $replacements
     * @return string valid regex
     */
    public function toPattern(array $replacements): string;

    /**
     * Gets valid callback function (if none an empty anonymous function is returned)
     * @return callable
     */
    public function getCallback(): ?callable;
}
