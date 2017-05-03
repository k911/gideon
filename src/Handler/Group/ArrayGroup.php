<?php
declare(strict_types=1);

namespace Gideon\Handler\Group;

use Traversable;
use ArrayIterator;
use IteratorAggregate;

/**
 * Group that can be used in loops and if return values of __call(...)
 * are objects, it returns group of this objects
 */
class ArrayGroup extends Base implements IteratorAggregate
{
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return ArrayGroup|mixed[]
     */
    public function __call(string $name, array $arguments = null): iterable
    {
        $results = parent::__call($name, $arguments);
        foreach ($results as $result) {
            if (!is_object($result)) {
                return $results;
            }
        }

        return (new ArrayGroup())->addMultiple($results);
    }
}
