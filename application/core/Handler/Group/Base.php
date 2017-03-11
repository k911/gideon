<?php
namespace Gideon\Handler\Group;

use Gideon\Debug\Base as Debug;
use Gideon\Handler\Group;

abstract class Base extends Debug implements Group
{
    private $items;

    /**
     * Adds single item
     * @param mixed $item
     * @throws Gideon\Handler\Group\InvalidArgumentException if $item is not an object
     * @return void
     */
    protected function addSingle($item)
    {
        if(is_object($item))
        {
            $this->items[] = $item;
        } 
        else throw new InvalidArgumentException("Added item is not an object.");
    }

    public function add(...$items): Group
    {
        return $this->addMultiple($items);
    }

    public function addMultiple(array $items): Group
    {
        foreach($items as $item)
            $this->addSingle($item);

        return $this;
    }

    public function __call(string $name, array $arguments): array
    {
        $results = [];
        foreach($this->items as $obj)
        {
            if(method_exists($obj, $name))
                $results[] = call_user_func_array([$obj, $name], $arguments);
            
            else 
            {
                $this->log("Couldn't call: " . get_class($obj) . "->$name(). Method doesn't exists.");
                $results[] = NULL;
            }
        }
        return $results;
    }

    public function __get(string $key): array
    {
        $results = [];
        foreach($this->items as $obj)
        {
            try 
            {
                $results[] = $obj->{$key};
            } 
            catch (\Exception $e)
            {
                $results[] = NULL;
                $this->log($e);
            }
        }
        return $results;
    }

    public function __set(string $key, $value): array
    {
        $results = [];
        foreach($this->items as $obj)
        {
            try 
            {
                $obj->{$key} = $value;
                $results[] = true;
            } 
            catch (\Exception $e)
            {
                $results[] = false;
                $this->log($e);
            }
        }
        return $results;
    }

    public function __isset(string $key): bool
    {
        foreach($this->items as $obj)
        {
            if(!isset($obj->{$key}))
                return false;
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