<?php

use PHPUnit\Framework\TestCase;
use Gideon\Handler\Group\MixedGroup;

class Foo2 
{
    public $foo;
    public $bar = 5;

    public function zerout(bool $e, bool $e2)
    {
        if($e && !$e2)
            $this->foo = $this->bar = 0;
    }
}

final class GroupTest extends TestCase 
{
    public function testBasic()
    {
        // create test data
        $foos = [];
        $iis = [];
        for($i = 10; $i < 20; ++$i)
        {
            $f = new Foo2();
            $f->foo = $i;
            $foos[] = $f;
            $iis[] = $i;
        }

        $group = new MixedGroup();
        $group->addMultiple($foos);
        
        // test __get()
        $this->assertEquals($iis, $group->foo);

        $addThis = new Foo2();
        $addThis->foo = -999;
        $addThis->bar = 1;
        $group->add($addThis);


        // test __set()
        $group->foo = 999;
        foreach($foos as $f)
        {
            if($f->bar == 5)
                $this->assertEquals(999, $f->foo);

            else {
                $this->assertEquals(-999, $f->foo);
                $this->assertEquals(1, $f->bar);
            }
        }

        // test __call()
        $group->zerout(true, false);
        foreach($foos as $f)
        {
            $this->assertEquals(0, $f->foo);
            $this->assertEquals(0, $f->bar);
        }

        //$group->notExistentMethod(); shows in log

    }
}