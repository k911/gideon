<?php
namespace Gideon\Handler;

/**
 * Group objects
 */
interface Group {

    /**
     * Add item(s) which is an object to group
     * @param mixed[] $items => $item1, $item2, ..., $itemN
     * @throws Gideon\Handler\Group\InvalidArgumentException
     * @return Gideon\Handler\Group
     */
    public function add(...$items): self;
    
    /**
     * Add mutilple items (must be object) to group
     * @param mixed[] $items
     * @throws Gideon\Handler\Group\InvalidArgumentException
     * @return Gideon\Handler\Group
     */
    public function addMultiple(array $items): self;

    /** 
     * Invokes $name($arguments) on all stored objects
     * @param string    $name
     * @param mixed[]   $arguments
     * @return \IteratorAggreagte [PHP 7.1 return: iterable]
     */
    public function __call(string $name, array $arguments): \IteratorAggregate;

    /**
     * Gets $key from each group iteam
     * @param string $key
     * @return array of results
     */
    public function __get(string $key): array;

    /**
     * Sets $key => $value to each group item
     * @param string    $key
     * @param mixed     $value
     * @return bool[]
     */
    public function __set(string $key, $value): array;

    /**
     * Checks wheter all items in group has set $key
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool;

}