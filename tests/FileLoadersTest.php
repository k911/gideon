<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Gideon\Config\SimpleConfig;
use Gideon\Filesystem\IOException;
use Gideon\Collection\FileLoader;
use Gideon\Debug\Provider as Debug;

final class FileLoadersTest extends TestCase {

    const TEST_CONFIG_LOADED = 'tstcnfglded';
    const TEST_CONTAINER_PATH = __DIR__ . DIRECTORY_SEPARATOR . 'TestCollections' . DIRECTORY_SEPARATOR;
    private $test1_not_afile = self::TEST_CONTAINER_PATH . 'NoSuchFile';
    private $test1_file = self::TEST_CONTAINER_PATH . 'TestArray';
    private $test1_contents = [
        'Name' => 'TestArray',
        'SomeKey1' => true,
        'SomeKey2' => false,
        'SomeKey3' => 123,
        'SomeKey4' => 'nothing',
        'SomeKey5' => [1,2,3,'string']
    ];
    private $test1_extension = [
        'SomeKey1' => 'OVERRIDEN',
        'SomeKey6' => 'NEW_KEY'
    ];

    public function testSimpleLoad()
    {
        $container = new FileLoader($this->test1_file);
        $this->assertNotEquals(0, count($container));

        $containerExtended = new FileLoader($this->test1_file, $this->test1_extension);
        $this->assertNotEquals(0, count($containerExtended));
        $this->assertGreaterThan(count($container), count($containerExtended));
    }

    public function testNotValidLoad() {
        $exception = null;
        try {
            $container = new FileLoader($this->test1_not_afile);
        } catch (IOException $err)
        {
            $exception = $err;
        }
        $this->assertNotEquals(null, $exception);
    }

    public function testDataIntegrity() {
        $container = new FileLoader($this->test1_file);

        foreach($this->test1_contents as $key => $value)
        {
            $this->assertEquals(true, $container->has($key));
            $this->assertEquals($value, $container->findOne($key));
            $this->assertEquals($value, $container->{$key});
            $this->assertEquals($value, $container($key));
        }

        [$value1, $value2, $value3] = $container->findMultiple(['SomeKey1', 'SomeKey2', 'SomeKey3']);
        $this->assertEquals($this->test1_contents['SomeKey1'], $value1);
        $this->assertEquals($this->test1_contents['SomeKey2'], $value2);
        $this->assertEquals($this->test1_contents['SomeKey3'], $value3);

        [$value4, $value5] = $container('SomeKey4', 'SomeKey5');
        $this->assertEquals($this->test1_contents['SomeKey4'], $value4);
        $this->assertEquals($this->test1_contents['SomeKey5'], $value5);
    }

    public function testExtension() {
        $container = new FileLoader($this->test1_file);
        $this->assertEquals(false, $container->isExtended());
        $count1 = count($container);

        $container = new FileLoader($this->test1_file, $this->test1_extension);
        $this->assertEquals(true, $container->isExtended());
        $this->assertGreaterThan($count1, count($container));

        $super_array = array_merge($this->test1_contents, $this->test1_extension);
        foreach($super_array as $key => $value)
        {
            $this->assertEquals($value, $container->findOne($key));
        }
    }

    public function testClear() {
        $container = new FileLoader($this->test1_file, $this->test1_extension);
        $this->assertEquals(true, $container->isExtended());
        $count1 = count($container);

        $container->clear();
        $this->assertEquals(false, $container->isExtended());
        $this->assertGreaterThan(count($container), $count1);
    }

    public function testConfigLoad() {
        $config = new SimpleConfig('test-test', self::TEST_CONTAINER_PATH);
        $this->assertGreaterThan(0, count($config));
        $this->assertEquals(self::TEST_CONFIG_LOADED, $config->get('TEST_CONFIG_LOADED'));
        $this->assertEquals($config->findOne('TEST_CONFIG_LOADED'), $config->get('TEST_CONFIG_LOADED'));
    }

}
