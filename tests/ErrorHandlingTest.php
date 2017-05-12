<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Gideon\Application\Config;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Http\ResponseException;
use Gideon\Handler\Call\SafeCall;

final class MyCustomException extends ResponseException
{}

class ErrorHandlingTest extends TestCase
{
    private $config;
    private $handler;

    public function setUp()
    {
        $this->config = $config = new Config('test');
        $this->handler = new ErrorHandler($config, $config->getLogger());
    }

    public function testResponseException()
    {
        $ERROR_TEXT = 'Custom error has occured.';
        $handler = $this->handler;
        $safe = (new SafeCall($handler, function(string $txt) {
            throw new MyCustomException($txt);
            return true;
        }))->setArguments($ERROR_TEXT);

        $this->assertNotEquals(true, $safe->call());
        $this->assertNotEquals(0, count($handler));
        $this->assertEquals(true, $handler->has(get_class(new MyCustomException('t'))));
        [$index, $err] = $handler->findOne();
        $this->assertEquals(0, $index);
        $this->assertEquals($ERROR_TEXT, $err->getMessage());
        $this->assertEquals(500, $err->getCode());
        $this->assertEquals('MY_CUSTOM', $err->getErrorCode());
        $this->assertEquals($handler->findOne(), $handler->findOne('MyCustomException'));
        $this->assertEquals(true, $handler->clear()->isEmpty());
    }
}
