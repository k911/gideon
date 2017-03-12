<?php

use PHPUnit\Framework\TestCase;
use Gideon\Handler\Config;
use Gideon\Cache\SimpleCache;

class Foo11
{
    public $etc = 10;
}

final class SimpleCacheTest extends TestCase
{
    private $config;

    public function setUp()
    {
        $this->config = new Config('test');
    }

    public function testInit()
    {
        $path = $this->config->get('CACHE_PATH');
        if(file_exists($path))
        {
            array_map('unlink', glob("$path/*"));
            rmdir($path);
        }
        $this->assertEquals(false, file_exists($path));

        $cache = new SimpleCache($this->config);

        $this->assertEquals(true, file_exists($path));
        $this->assertEquals(true, is_dir($path));
        $this->assertNotEquals(true, $cache->get('ANYTHING', false));
        return $cache;
    }

    /**
     * @depends testInit
     */
    public function testCacheSaveRead($cache)
    {
        // test variables
        $i = 10;
        $s = "string";
        $array = ["string" => $s, "int" => $i];
        $sobj = new \stdClass();
        $sobj->i = $i;
        $sobj->s = $s;
        $sobj->array = $array;

        // Test set
        $this->assertEquals(true, $cache->set('I', $i));
        $this->assertEquals(true, $cache->set('S', $s));
        $this->assertEquals(true, $cache->set('Array', $array));
        $this->assertEquals(true, $cache->set('Sobj', $sobj));
        $this->assertEquals(true, $cache->set('etc', new Foo11()));

        // Test get
        $this->assertSame($i, $cache->get('I'));
        $this->assertSame($s, $cache->get('S'));
        $this->assertSame($array, $cache->get('Array'));
        $this->assertEquals($sobj, $cache->get('Sobj'));
        $this->assertEquals(new Foo11(), $cache->get('etc'));
        $this->assertNotEquals($sobj, $cache->get('sobj'));
        return $cache;
    }

    /**
     * @depends testCacheSaveRead
     */
    public function testClear($cache)
    {
        $cache->clear();
        $this->assertEquals(false, (new \FilesystemIterator($this->config->get('CACHE_PATH')))->valid());
    }
}