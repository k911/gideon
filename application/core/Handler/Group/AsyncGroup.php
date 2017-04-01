<?php
namespace Gideon\Handler\Group;

use Gideon\Handler\Group;

/**
 * @todo test
 * Due to performance reasons, most of the burden is moved 
 * onto object initialization / addition functions
 * becouse it speeds up execution time of calls
 */
class AsyncGroup extends UniformGroup implements \IteratorAggregate
{
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function __construct(string $uniform)
    {  
        // Due to performance reasons most of the burden 
        // is moved on the intialization/creation pro 
        parent::__construct($uniform, true);
    }

    public function __call(string $name, array $args): \IteratorAggregate
    {
        $results = new AsyncGroup($this->uniform);
        $workers = [];

        // create and start threads
        foreach($this->items as $item)
            $workers[] = (new AsyncGroup\Worker($name, $args))->start();

        // join the results
        foreach($workers as $worker)
        {
            $worker->join();
            $results[] = $worker->result;
        }
        return $results;
    }

}