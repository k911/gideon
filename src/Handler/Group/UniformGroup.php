<?php
declare(strict_types=1);

namespace Gideon\Handler\Group;

use ReflectionClass;
use Gideon\Exception\Warning;

/**
 * Group accepting only instances of specific objects
 */
class UniformGroup extends Base
{
    protected $uniform;
    protected $strict;

    protected function verify($item): bool
    {
        // Verify
        $r = false;
        if ($this->strict) {
            $r = get_class($item) === $this->uniform;
        } else {
            $r = $item instanceof $this->uniform;
        }

        // Log
        if (!$r) {
            $strict_txt = ($this->strict) ? "ON" : "OFF";
            $class_name = (new ReflectionClass($item))->getShortName();
            $this->getErrorHandler()->log(new Warning("Object `$class_name` is not compatible uniform for `{$this->uniform}`. Strict mode $strict_txt."));
        }
        return $r;
    }

    /**
     * @param string $unfiorm class or interface name
     * @param bool $strict_types when trun, object added to group must be exactly same type
     */
    public function __construct(string $uniform, bool $strict_types = false)
    {
        if (!interface_exists($uniform, true) && !class_exists($uniform, true)) {
            throw new InvalidArgumentException("Class or interface `$uniform` doesn't exists.");
        }

        $this->uniform = $uniform;
        $this->strict = $strict_types;
    }
}
