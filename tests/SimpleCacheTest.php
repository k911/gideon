<?php
declare(strict_types=1);

use Gideon\Filesystem\Directory;
use PHPUnit\Framework\TestCase;
use Gideon\Config\SimpleConfig;
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
        $this->config = new SimpleConfig('test');
    }

    public function testInit()
    {
        $path = $this->config->get('CACHE_PATH');
        $dir = new Directory($path);
        if($dir->exists()) {
            $dir->delete();
        }

        $cache = new SimpleCache($this->config);

        $this->assertEquals(true, $dir->exists());
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
        $sobj = new stdClass();
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
        $path = $this->config->get('CACHE_PATH');
        $cache->clear();
        // assert no files in cache dir
        $this->assertNotEquals(true, (new \FilesystemIterator($path))->valid());
        rmdir($path);
        $this->assertNotEquals(true, file_exists($path));
    }
}
