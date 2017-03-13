<?php
namespace Gideon\Handler\Group;

/**
 * Group that can be used in loops and if return values of __call(...)
 * are objects, it returns group of this objects
 */
class ArrayGroup extends MixedGroup implements \IteratorAggregate
{
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function __call(string $name, array $arguments): \IteratorAggregate
    {
        // \ArrayObject
        $results = parent::__call($name, $arguments);
        foreach($results as $result)
        {
            if(!is_object($result))
                return $results;
        }

        // TODO: PHP 7.1
        return (new ArrayGroup())->addMultiple($results->getArrayCopy());
    }
}