<?php
namespace Gideon\Debug;

use Gideon\Debug;
use Gideon\Handler\Config;
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
 * - LOGGER_ROOT
 */
abstract class Provider implements Debug
{
    /**
     * @var Gideon\Debug\Logger $Logger
     */
    protected static $Logger;

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
     * @return Gideon\Debug\Logger
     */
    public function getLogger(): LoggerInterface
    {
        $className = isset($this) ? get_class($this) : get_called_class();
        return self::$Logger->withPrefix($className);
    }

    /**
     * Intialize Gideon\Debug\Logger object
     * @param Gideon\Handler\Config $config
     * @return bool success
     */
    public static function initLogger(Config $config): bool
    {
        if (!isset(self::$Logger)) {
            $logfile = $config->get('LOGGER_FILE');
            $clear = $config->get('LOGGER_RESET_LOG') === true;

            // Create logger
            $logger = (new Logger($logfile))->withPrefix('Debug\Provider');

            // Clear logfile if wanted
            if ($clear) {
                $logger->clear()->info('Reset');
            }
            self::$Logger = $logger;
        }
        return true;
    }
}
