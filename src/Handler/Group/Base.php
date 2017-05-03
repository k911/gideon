<?php
declare(strict_types=1);

namespace Gideon\Handler\Group;

use Gideon\Debug\Provider as Debug;
use Gideon\Handler\Group;

abstract class Base extends Debug implements Group
{
    /**
     * @var mixed[] $items
     */
    protected $items;

    /**
     * Check if added item is supported
     * @param mixed $item
     * @return bool
     */
    protected function verify($item): bool
    {
        $r = is_object($item);
        if (!$r) {
            $this->getLogger()->warning("Provided item is not an object.");
        }
        return $r;
    }

    public function add(...$items): Group
    {
        return $this->addMultiple($items);
    }

    public function addMultiple(iterable $items): Group
    {
        foreach ($items as $item) {
            if (!$this->verify($item)) {
                throw new InvalidArgumentException("Provided item for addition is invalid.");
            }
            $this->items[] = $item;
        }
        return $this;
    }

    public function __call(string $name, array $arguments = null): iterable
    {
        $results = [];
        $args = is_null($arguments);
        foreach ($this->items as $obj) {
            if (!method_exists($obj, $name)) {
                throw new InvalidArgumentException("Couldn't call: " . get_class($obj) . "->$name(). Method doesn't exists.");
            }

            $results[] = ($args) ?
                call_user_func([$obj, $name]) :
                call_user_func_array([$obj, $name], $arguments);
        }
        return $results;
    }

    public function __get(string $key): iterable
    {
        $results = [];
        foreach ($this->items as $obj) {
            $results[] = $obj->{$key};
        }
        return $results;
    }

    public function __set(string $key, $value)
    {
        $results = [];
        foreach ($this->items as $obj) {
            $obj->{$key} = $value;
        }
        return $results;
    }

    public function __isset(string $key): bool
    {
        foreach ($this->items as $obj) {
            if (!isset($obj->{$key})) {
                return false;
            }
        }
        return true;
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return ['items' => $this->items];
    }
}
