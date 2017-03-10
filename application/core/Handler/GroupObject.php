<?php

namespace Gideon\Handler;

use Gideon\Debug\Base as Debug;

class GroupObject extends Debug
{
    private $objects;

    public function import(array $objects): self
    {
        foreach($objects as $obj)
        {
            $this->add($obj);
        }
        return $this;
    }

    public function add($object): self
    {
        if(is_object($object))
        {
            $this->objects[] = $object;
        }
        else $this->log("Adding failed. Value: '" . var_export(true) . "' is not an object.");
        return $this;
    }

    /** 
     * Invokes method $name on all stored objects
     * @param string $name
     * @param mixed[] $arguments
     * @return Gideon\Handler\GroupObject $this
     */
    public function __call(string $name, array $arguments)
    {
        foreach($this->objects as $obj)
        {
            if(method_exists($obj, $name))
                call_user_func_array([$obj, $name], $arguments);
            else $this->log("Couldn't call: " . get_class($obj) . "->$name()");
        }
        return $this;
    }

    public function __get($key)
    {
        $results = [];
        foreach($this->objects as $obj)
        {
            if(property_exists($obj, $key))
                $results[] = $obj->{$key};
        }
        return $results;
    }

    public function __set($key, $value)
    {
        foreach($this->objects as $obj)
        {
            if(property_exists($obj, $key))
                $obj->{$key} = $value;
        }
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return [];
    }
}