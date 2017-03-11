<?php

use PHPUnit\Framework\TestCase;
use Gideon\Handler\Config;
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
    private $config;

    public function __construct()
    {
        $this->config = new Config('test');
    }

    public function testIsset()
    {
        $obj1 = new Foo2();
        $obj2 = new Foo2();
        $obj2->foo = 10;

        $this->assertEquals(true, is_object($obj1));
        $this->assertEquals(true, is_object($obj2));

        $group = new MixedGroup();
        $group->add($obj1, $obj2);

        // test isset
        $this->assertEquals(false, isset($obj1->foo));
        $this->assertEquals(true, isset($obj2->foo));
        $this->assertEquals(false, isset($group->foo));
        $obj1->foo = 'x';
        $this->assertEquals(true, isset($group->foo)); 
    }

    public function testBase()
    {
        // create test data
        $foos = [];
        $iis = [];
        for($i = 10; $i < 20; ++$i)
        {
            $foos[] = $f = new Foo2();
            $f->foo = $i;
            $iis[] = $i;
        }

        $group = new MixedGroup();
        $group->addMultiple($foos);
        
        // test __get()
        $this->assertEquals($iis, $group->foo);

        // add one more
        $addThis = new Foo2();
        $addThis->foo = -999;
        $addThis->bar = 1;
        $group->add($addThis);

        // test __set()
        $this->assertEquals(true, $group->foo = 999);
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

        //$group->notExistentMethod(); //shows in log

    }
}