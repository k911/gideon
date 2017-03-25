<?php
namespace Gideon\Handler\Group;

/**
 * Group accepting only instances of specific objects
 */
class UniformGroup extends Base 
{
    protected $uniform;
    protected $strict;

    protected function addSingle($item)
    {
        if(($this->strict) ? (get_class($item) !== $this->uniform) : (!($item instanceof $this->uniform)))
        {
            $obj = get_class($item);
            $strict = $this->strict ? 'on' : 'off';
            throw new InvalidArgumentException("Object $obj is not instance of {$this->uniform}. Strict mode $strict.");
        }
        
        parent::addSingle($item);
    }

    public function __construct(string $uniform, bool $strict = false)
    {
        if(!interface_exists($uniform, true) && !class_exists($uniform, true))
            throw new InvalidArgumentException("Class or interface `$uniform` doesn't exists.");

        $this->uniform = $uniform;
        $this->strict = $strict;
    }
}