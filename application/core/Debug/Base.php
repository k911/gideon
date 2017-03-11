<?php
namespace Gideon\Debug;

use Gideon\Debug;

abstract class Base implements Debug 
{

    /**
     * Function should return actual array of dependencies
     * that are wanted in getDebugDetails function
     * @return array key => name, value => mixed;
     */
    abstract protected function getDebugProperties(): array;

    public function getDebugDetails(): array
    {
        $data = $this->getDebugProperties();
        foreach($data as $index => $dependency) 
        {
            if($dependency instanceof Debug)
                $data[$index] = $dependency->getDetails();

            elseif (is_array($dependency) || $dependency instanceof \ArrayObject)
            {
                foreach($dependency as $i => $item)
                {
                    if($item instanceof Debug)
                    {
                        $data[$index][$i] = $item->getDetails();
                    }
                }
            }
        }
        $data['class'] = get_class($this);
        return $data;
    }

    public function showDebugDetails($json = false)
    {
        $details = $this->getDebugDetails();

        if($json)
            echo '<pre>' . json_encode($details, JSON_PRETTY_PRINT) . '</pre>';
        else
            var_dump($details);
    }

    public function log(string $what): bool
    {
        if($what instanceof \Throwable)
            $name = get_class($what);
        else
            $name = isset($this) ? get_class($this) : get_called_class();
        return Logger::log($name, $what);
    }

}