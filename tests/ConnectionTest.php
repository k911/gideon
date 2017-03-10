<?php

use PHPUnit\Framework\TestCase;
use Gideon\Handler\Config;
use Gideon\Database\Connection;
use Gideon\Debug\Base as Debug;

final class ConnectionTest extends TestCase
{
    private $config;

    public function setUp()
    {
        $this->config = new Config('test');
    }

    public function testMySQL()
    {
        $mysql = new Connection\MySQL($this->config);
        $this->assertEquals(true, $mysql->try_connect());
        $mysql->close();
    }

    public function testMySQLPort()
    {
        $options = ['host' => $this->config->get('TEST_MYSQL_HOST'), 'port' => $this->config->get('TEST_MYSQL_PORT')];
        $mysql = new Connection\MySQL($this->config, $options);
        $this->assertEquals(true, $mysql->try_connect());
        $mysql->close();

        ++$options['port'];
        $mysql = new Connection\MySQL($this->config, $options);
        $mysql->log('Error 2002 should be upcoming next.');
        $this->assertNotEquals(true, $mysql->try_connect());
        $mysql->close();
    }

}