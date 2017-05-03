<?php

use PHPUnit\Framework\TestCase;
use Gideon\Handler\Config;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Database\Connection;
use Gideon\Debug\Provider as Debug;

final class ConnectionTest extends TestCase
{
    private $config;
    private $handler;

    public function setUp()
    {
        $this->config = $config = new Config('test');
        $this->handler = new ErrorHandler($config, $config->getLogger());
    }

    public function testMySQL()
    {
        $mysql = new Connection\MySQL($this->config);
        $this->assertEquals(true, $mysql->try_connect($this->handler));
        $this->assertEquals(true, $this->handler->isEmpty());
        $mysql->close();
    }

    public function testMySQLPort()
    {
        $options = ['host' => $this->config->get('TEST_MYSQL_HOST'), 'port' => $this->config->get('TEST_MYSQL_PORT')];
        $mysql = new Connection\MySQL($this->config, $options);
        $this->assertEquals(true, $mysql->try_connect($this->handler));
        $this->assertEquals(true, $this->handler->isEmpty());
        $mysql->close();

        ++$options['port'];
        $mysql = new Connection\MySQL($this->config, $options);
        $this->assertNotEquals(true, $mysql->try_connect($this->handler));
        $this->assertNotEquals(true, $this->handler->isEmpty());
        $mysql->close();
    }

}
