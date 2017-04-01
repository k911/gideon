<?php

use PHPUnit\Framework\TestCase;
use Gideon\Handler\Config;
use Gideon\Debug\Provider as Debug;

final class ConfigTest extends TestCase 
{
    private $config;

    const TEST_CONFIG_LOADED = 'tstcnfglded';

    public function setUp()
    {
        $this->config = new Config('test');
    }

    public function testLoaded()
    {
        $this->assertEquals(true, $this->config->has('TEST_CONFIG_LOADED'));
        $this->assertEquals(self::TEST_CONFIG_LOADED, $this->config->get('TEST_CONFIG_LOADED'));
    }

    public function testExtend()
    {
        $array = [
            'testing' => true,
            'someValue' => 10,
            'TEST_CONFIG_LOADED' => self::TEST_CONFIG_LOADED . '-o'
        ];
        $c = $this->config;
        $c->extend($array);
        $this->assertEquals(true, $c->has('testing'));
        $this->assertEquals($array['TEST_CONFIG_LOADED'], $c->get('TEST_CONFIG_LOADED'));
        $this->assertEquals(self::TEST_CONFIG_LOADED, $c->get('TEST_CONFIG_LOADED', true));
        $c->extend(['someValue' => -1]);
        $this->assertNotEquals($array['someValue'], $c->get('someValue'));
        $c->extend(['testing' => false], true);
        $this->assertEquals(true, $c->has('testing'));
        $this->assertEquals(false, $c->has('testing', true));
        $this->assertEquals(false, $c->has('someValue'));
        $this->assertNotEquals($array['testing'], $c->get('testing'));
    }
}