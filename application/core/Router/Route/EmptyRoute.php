<?php
namespace Gideon\Router\Route;

use Gideon\Http\Request;
use Gideon\Router\Route;
use Gideon\Debug\Base as Debug;

/**
 * Empty implementation of Route interface
 * Dispatcher returns it in case of no match for the given Request.
 */
class EmptyRoute extends Debug implements Route 
{
    public function map(Request $request): array
    {
        return [];
    }
    public function size(): int
    {
        return -1;
    }
    public function empty(): bool
    {
        return true;
    }
    public function regex(array $replacements): string 
    {
        return '';
    }
    public function where(array $replacements): Route
    {
        return $this;
    }
    public function handler()
    {
        return null;
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return [];
    }
} 