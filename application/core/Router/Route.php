<?php
namespace Gideon\Router;

use Gideon\Http\Request;

interface Route 
{
    public function map(Request $request): array;
    public function size(): int;
    public function empty(): bool;
    public function regex(array $replacements): string;
    public function where(array $replacements): self;
    public function handler();
}