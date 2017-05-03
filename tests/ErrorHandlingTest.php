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
        $this->assertEquals($ERROR_TEXT, $handler->getFirst()->getMessage());
        $this->assertEquals(500, $handler->getFirst()->getCode());
        $this->assertEquals('MY_CUSTOM', $handler->getFirst()->getErrorCode());
        $this->assertEquals(true, $handler->clear()->isEmpty());
    }
}
