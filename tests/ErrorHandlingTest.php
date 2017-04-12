<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Gideon\Handler\Config;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Exception\ErrorResponseException;

final class MyCustomError extends ErrorResponseException
{}

class ErrorHandlingTest extends TestCase
{
    private $config;
    private $handler;

    public function setUp()
    {
        $this->config = $config = new Config('test');
        $this->handler = new ErrorHandler($config->get('LOGGER_ROOT'));
    }

    public function testErrorResponse()
    {
        $errs = $this->handler;
        $thrownMess = 'test_message';

        $errs->handle(function($mess) {
            throw new MyCustomError($mess);
        }, $thrownMess);
        $this->assertNotEquals(0, count($errs));
        $this->assertEquals(true, $errs->has(get_class(new MyCustomError('d'))));
        $this->assertEquals($thrownMess, $errs->getFirst()->getMessage());
        $this->assertEquals(500, $errs->getFirst()->getCode());
        $this->assertEquals('MY_CUSTOM', $errs->getFirst()->getErrorCode());
    }
}
