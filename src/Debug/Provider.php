<?php
namespace Gideon\Debug;

use ReflectionClass;
use Gideon\Debug;
use Gideon\Config;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Exception\Fatal;
use Psr\Log\LoggerInterface;

/**
 * Debug class which provides basic abilities
 * for proper object debuging
 * Usage: extend your object with this class, and configure
 *        getDebugProperties method
 *
 * Config keys used:
 * - LOGGER_RESET_LOG
 * - LOGGER_FILE
 * - LOGGER_DIR
 * - LOGGER_ROOT
 */
abstract class Provider implements Debug
{
    /**
     * @var \Gideon\Debug\Logger $Logger
     */
    protected static $Logger;

    /**
     * @var \Gideon\Handler\ErrorHandler $ErrorHandler
     */
    protected static $ErrorHandler;

    /**
     * Gets array of dependecies used to show in function getDebugDetails
     * @return mixed[] string => dependency
     */
    abstract protected function getDebugProperties(): array;

    public function getDebugDetails(): array
    {
        $data = $this->getDebugProperties();
        foreach ($data as $index => $dependency) {
            if ($dependency instanceof Debug) {
                $data[$index] = $dependency->getDebugDetails();
            } elseif (is_array($dependency) || $dependency instanceof \ArrayObject) {
                foreach ($dependency as $i => $item) {
                    if ($item instanceof Debug) {
                        $data[$index][$i] = $item->getDebugDetails();
                    }
                }
            }
        }
        $data['class'] = get_class($this);
        return $data;
    }

    public function showDebugDetails($json = false)
    {
        $details = $this->getDebugDetails();

        if ($json) {
            echo '<pre>' . json_encode($details, JSON_PRETTY_PRINT) . '</pre>';
        } else {
            var_dump($details);
        }
    }

    /**
     * Gets logger instance with called class name as prefix
     * @return \Gideon\Debug\Logger
     */
    public function getLogger(): LoggerInterface
    {
        if(!isset(self::$Logger))
            throw new Fatal('Trying to access uninitialized logger');
        return self::$Logger->withPrefix(isset($this) ? (new ReflectionClass($this))->getShortName() : get_called_class());
    }

    /**
     * Gets error handler instance
     * @return \Gideon\Handler\ErrorHandler
     */
    public static function getErrorHandler(): ErrorHandler
    {
        if(!isset(self::$Logger))
            throw new Fatal('Trying to access uninitialized error handler');
        return self::$ErrorHandler;
    }

    private static function setUpLogger(Config $config): Logger
    {
        $loggerDir = $config->get('LOGGER_DIR');
        $loggerFile = $config->get('LOGGER_FILE');
        $clear = $config->get('LOGGER_RESET_LOG') === true;

        // Create logger
        $logger = new Logger($loggerDir, $loggerFile);

        // Clear logfile if wanted
        if ($clear) {
            $logger->clear()->withPrefix('Debug\Provider')->info('Reset');
        }

        return $logger;
    }

    private static function setUpErrorHandler(Config $config, Logger $logger): ErrorHandler
    {
        // Create Error Handler instance
        $errorHandler = new ErrorHandler($config, $logger);
        $errorHandler->fullErrorHandling();
        return $errorHandler;
    }

    /**
     * Intialize Gideon\Debug\Logger object
     * @param \Gideon\Config $config
     * @return bool success
     */
    public static function initDebugProvider(Config $config): bool
    {
        if (!isset(self::$Logger) || !isset(self::$ErrorHandler)) {

            self::$Logger = $logger = self::setUpLogger($config);
            self::$ErrorHandler = self::setUpErrorHandler($config, $logger);
        }
        return true;
    }
}
