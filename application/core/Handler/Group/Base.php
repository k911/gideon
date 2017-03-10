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
    private function addSingle($item)
    {
        if(is_object($item))
        {
            $this->items[] = $item;
        } 
        else throw new InvalidArgumentException("Added item is not an object.");
    }

    public function add(...$items): Group
    {
        foreach($items as $item)
            $this->addSingle($item);

        return $this;
    }

    public function addMultiple(array $items): Group
    {
        foreach($items as $item)
            $this->addSingle($items);

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
            if(method_exists($obj, '__get'))
            {
                $results[] = $obj->{$key};
            }
            else 
            {
                $this->log("Unable to get property: " . get_class($obj) . "->$key.");
                $results[] = NULL;
            }
        }
        return $results;
    }

    public function __set(string $key, $value): array
    {
        $results = [];
        foreach($this->items as $obj)
        {
            if(method_exists($obj, '__set'))
            {
                // Remarks: when __set returns null it uses __get to verify wheter operation 
                //          was successful or not
                $results[] = ($obj->{$key} = $value) ?? ($obj->{$key} === $value); 
            }
            else 
            {
                $this->log("Unable to get property: " . get_class($obj) . "->$key.");
                $results[] = false;
            }
        }
        return $results;
    }

    public function __isset(string $key): bool
    {
        foreach($this->items as $obj)
        {
            if(method_exists($obj, '__isset'))
            {
                if(!isset($obj->{$key}))
                    return false;
            }
            else 
            {
                $this->log("Trying to use not implemented isset on object: " . get_class($obj));
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