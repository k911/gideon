<?php


use PHPUnit\Framework\TestCase;
use Gideon\Debug\Logger;
use Gideon\Handler\Config;

class ExtendedLogger extends Logger 
{

    public static function delete()
    {
        self::$Logger = null;
    }

    public function isset(): bool
    {
        return isset(self::$Logger);
    }
}

final class DebugLoggerTest extends TestCase
{

    private $config;

    public function setUp()
    {
        $this->config = new Config('test');
        $this->assertEquals(false, ($this->config->get('LOGGER_RESET_LOG') === true));
    }

    public function testInit()
    {
        ExtendedLogger::delete();
        $this->assertEquals(false, ExtendedLogger::isset());
        $testfile = $this->config->get('TEST_LOGFILE');
        if(file_exists($testfile))
            unlink($testfile);

        $this->config->extend(['LOGGER_FILE' => $testfile]);
        $this->assertEquals(true, Logger::init($this->config));
        $this->assertEquals(true, file_exists($testfile));
        $this->assertNotEquals('', file_get_contents($testfile));
        return $testfile;
    }

    /**
     * @depends testInit
     */
    public function testLog($logfile)
    {
        $this->assertEquals(true, ExtendedLogger::isset());
        $this->assertEquals(true, file_exists($logfile));
        $this->assertNotSame(false, file_put_contents($logfile, ''));
        $this->assertEquals('', file_get_contents($logfile));
        $this->assertEquals(true, Logger::log('Something', 'some log'));
        $this->assertNotEquals('', file_get_contents($logfile));

        unlink($logfile);
        ExtendedLogger::delete();
    }

    public function testInitReset()
    {
        ExtendedLogger::delete();
        $this->assertEquals(false, ExtendedLogger::isset());
        $testfile = $this->config->get('TEST_LOGFILE');
        $this->config->extend(['LOGGER_FILE' => $testfile]);

        // Saving some values into clear log
        if(file_exists($testfile))
            unlink($testfile);
        $this->assertEquals(true, Logger::init($this->config));
        $this->assertEquals(true, Logger::log('A', 'something more'));
        $this->assertEquals(true, Logger::log(0, new Error('test')));
        $this->assertEquals(true, Logger::log('DD', ['something more', 'yes']));
        $size1 = strlen(file_get_contents($testfile));

        // Testing that previous values will be keeped when initialized again without reset
        ExtendedLogger::delete();
        $this->assertEquals(false, ExtendedLogger::isset());
        $this->assertEquals(true, Logger::init($this->config));
        $this->assertEquals(true, Logger::log('X'));
        $size2 = strlen(file_get_contents($testfile));
        $this->assertGreaterThan($size1, $size2);

        // Testing reset option
        ExtendedLogger::delete();
        $this->assertEquals(false, ExtendedLogger::isset());
        $this->config->extend(['LOGGER_RESET_LOG' => true]);
        $this->assertEquals(true, Logger::init($this->config));
        $this->assertEquals(true, file_exists($testfile));
        $size3 = strlen(file_get_contents($testfile));
        $this->assertGreaterThan($size3, $size1);

        ExtendedLogger::delete();
        unlink($testfile);
    }
}