<?php

// TODO: Make proper tests
// use PHPUnit\Framework\TestCase;
// use Gideon\Debug\Provider;
// use Gideon\Debug\Logger;
// use Gideon\Application\Config;

// final class TestLogProvider extends Provider 
// {
//     protected function getDebugProperties(): array
//     {
//         return ['logger' => self::$Logger];
//     }

//     public static function delete()
//     {
//         self::$Logger = null;
//     }

//     public static function getLogger()
//     {
//         return self::$Logger;
//     } 

//     public function isset(): bool
//     {
//         return isset(self::$Logger);
//     }
// }

// final class DebugLoggerTest extends TestCase
// {

//     private $config;

//     public function setUp()
//     {
//         $this->config = new Config('test');
//         $this->assertEquals(false, ($this->config->get('LOGGER_RESET_LOG') === true));
//     }

//     public function testInit()
//     {
//         TestLogProvider::delete();
//         $this->assertEquals(false, TestLogProvider::isset());
//         $testfile = $this->config->get('TEST_LOGFILE');
//         if(file_exists($testfile))
//             unlink($testfile);

//         $this->config->extend(['LOGGER_FILE' => $testfile]);
//         $this->assertEquals(true, TestLogProvider::initDebugProvider($this->config));
//         $this->assertEquals(true, file_exists($testfile));
//         $this->assertNotEquals('', file_get_contents($testfile));
//         return $testfile;
//     }

//     /**
//      * @depends testInit
//      */
//     public function testLog($logfile)
//     {
//         $this->config->getLogger()->error('WTF');
//         $this->assertEquals(true, TestLogProvider::isset());
//         $this->assertEquals(true, file_exists($logfile));
//         $this->assertNotSame(false, file_put_contents($logfile, ''));
//         $this->assertEquals('', file_get_contents($logfile));
//         TestLogProvider::getLogger()->error('Something');
//         $this->assertNotEquals('', file_get_contents($logfile));

//         unlink($logfile);
//         TestLogProvider::delete();
//     }

//     public function testInitReset()
//     {
//         TestLogProvider::delete();
//         $this->assertEquals(false, TestLogProvider::isset());
//         $testfile = $this->config->get('TEST_LOGFILE');
//         $this->config->extend(['LOGGER_FILE' => $testfile]);

//         // Saving some values into clear log
//         if(file_exists($testfile))
//             unlink($testfile);
//         $this->assertEquals(true, TestLogProvider::initDebugProvider($this->config));
//         TestLogProvider::getLogger()->error('something more');
//         TestLogProvider::getLogger()->error(new Error('test'));
//         TestLogProvider::getLogger()->error(['something more', 'yes']);
//         $size1 = strlen(file_get_contents($testfile));

//         // Testing that previous values will be keeped when initialized again without reset
//         TestLogProvider::delete();
//         $this->assertEquals(false, TestLogProvider::isset());
//         $this->assertEquals(true, TestLogProvider::initDebugProvider($this->config));
//         TestLogProvider::getLogger()->error('X');
//         $size2 = strlen(file_get_contents($testfile));
//         $this->assertGreaterThan($size1, $size2);

//         // Testing reset option
//         TestLogProvider::delete();
//         $this->assertEquals(false, TestLogProvider::isset());
//         $this->config->extend(['LOGGER_RESET_LOG' => true]);
//         $this->assertEquals(true, TestLogProvider::initDebugProvider($this->config));
//         $this->assertEquals(true, file_exists($testfile));
//         $size3 = strlen(file_get_contents($testfile));
//         $this->assertGreaterThan($size3, $size1);

//         TestLogProvider::delete();
//         unlink($testfile);
//     }
// }