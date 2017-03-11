<?php
namespace Gideon\Debug;

use Gideon\Handler\Config;

/**
 * Config keys used:
 * - LOGGER_RESET_LOG
 * - LOGGER_FILE
 * - LOGGER_ROOT
 */

class Logger 
{
    /**
     * @var Gideon\Debug\Logger $Logger
     * @var string              $root        how deep should string with pathes in messages should be logged
     * @var string              $logfile     path to writable/creatable file
     */
    protected static $Logger;
    protected $root;
    protected $logfile;

    protected function parseThrowable(\Throwable $thrown): string
    {
        $file = substr($thrown->getFile(), strpos($thrown->getFile(), $this->root) + strlen($this->root)); 
        $message = preg_replace("~class\@anonymous[^\s\'\"\,]*~", 'class@anonymous', $thrown->getMessage());
        return "[{$thrown->getCode()}] `$file:{$thrown->getLine()}` $message";
    }

    /**
     * Write log line containg timestamp, id and message
     * Tries to save it in utf-8 encoding using this hack:
     * @link http://stackoverflow.com/questions/7979567/php-convert-any-string-to-utf-8-without-knowing-the-original-character-set-or
     *
     * @param string    $id
     * @param mixed     $what message
     */
    public function write(string $id, $what): bool
    {
        if($what instanceof \Throwable)
        {
            $what = $this->parseThrowable($what);
        }
        elseif (!is_string($what))
        {
            $what = serialize($what);
        }

        // Convert to UTF-8 and remove newline from endings
        $what = trim($what);
        $what = iconv(mb_detect_encoding($what, mb_detect_order(), true), "UTF-8", $what);

        $timestamp = time();
        $line = "$timestamp\t[$id]" . (empty($what) ? '' : ":\t$what") . PHP_EOL;
        return (file_put_contents($this->logfile, $line, FILE_APPEND) !== false);
    }

    private function __construct(string $logfile, string $root)
    {
        $this->logfile = $logfile;
        $this->root = $root;
    }

    /**
     * Save log
     * @param string    $id useful to know what produced log 
     * @param mixed     $what preferably string or \Throwable, anything else is serialized
     * @return bool     true on success | false otherwise
     */
    public static function log(string $id, $what = null): bool
    {
        return isset(self::$Logger) ? self::$Logger->write($id, $what) : false;
    }

    public static function init(Config $config): bool
    {
        if(!isset(self::$Logger))
        {
            $logfile = $config->get('LOGGER_FILE');
            $root = $config->isset('LOGGER_ROOT') ? $config->get('LOGGER_ROOT') : 'vendor';

            if(!file_exists($logfile) && !touch($logfile))
                return false;
            
            $reset = ($config->get('LOGGER_RESET_LOG') === true);
            if($reset && file_put_contents($logfile, '') === false)
                return false;

            self::$Logger = new Logger($logfile, $root);
            self::log(($reset ? 'Reset' : 'Init'), '');
        }
        return true;
    }

}