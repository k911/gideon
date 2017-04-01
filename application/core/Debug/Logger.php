<?php
namespace Gideon\Debug;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * PSR-3 Logger implementation
 */
class Logger extends AbstractLogger
{
    /**
     * @var string $root how deep should string with pathes in messages should be logged
     */
    protected $root;

    /**
     * @var string $logfile path to writable/creatable file
     */
    protected $logfile;
    
    /**
     * @var string $prefix to write in log line after log level 
     */
    protected $prefix;

    /**
     * @param string $logfile
     *      @see $this->logfile
     * @param string $root 
     *      @see $this->root
     */
    public function __construct(string $logfile, string $root)
    {
        $this->logfile = $logfile;
        $this->root = $root;
    }

    /**
     * Changes prefix and returns itself
     * @param string $prefix
     * @return Gideon\Debug\Logger
     */
    public function withPrefix(string $prefix): Logger
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Main log function
     * @param string $level 
     *      @see Psr\Log\LogLevel
     * @param string|\Throwable|\Serializable $message 
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        // Parse object to string
        if(!is_string($message))
        {
            if($message instanceof \Throwable)
                $message = $this->parseThrowable($message);
            
            elseif (!is_array($message) && (!is_object($message) || method_exists($message, '__toString')))
                $message = (string)$message;
            
            // TODO: throw exception
            else $message = serialize($message);
        }
        
        // Replace templates
        if(!empty($context))
            $message = $this->interpolate($message, $context);
        
        // Add prefix
        if(!empty($this->prefix))
            $message = "[{$this->prefix}] $message";
        
        // Create and save line to log file
        $timestamp = time();
        $level = ($level === LogLevel::ERROR || $level === LogLevel::CRITICAL || $level === LogLevel::ALERT || $level === LogLevel::EMERGENCY) ? 
            "! $level" : (
            ($level === LogLevel::DEBUG || $level === LogLevel::INFO) ? 
            "  $level" :
            "- $level" );
        $this->writeln("$timestamp $level:\t$message");
    }

    /**
     * Write log line
     * Tries to save it in utf-8 encoding using this hack:
     * @link http://stackoverflow.com/questions/7979567/php-convert-any-string-to-utf-8-without-knowing-the-original-character-set-or
     * @param string $line
     * @throws IOException
     * @return void
     */
    protected function writeln(string $line)
    {
        // Convert to UTF-8 and remove newline from endings
        $line = trim($line);
        $line = iconv(mb_detect_encoding($line, mb_detect_order(), true), "UTF-8", $line);
        
        if(file_put_contents($this->logfile, $line . PHP_EOL, FILE_APPEND) === false)
            ; // TODO: throw IOException
    }

    /**
     * Interpolates context values into the message placeholders.
     * @param string $message with optional templates: {template_name}
     * @param array $context replacement => convertable to string value 
     * @return string interpolated
     */
    protected function interpolate(string $message, array $context = []): string
    {
        // build a replacement array with braces around the context keys
        $replace_pairs = [];
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace_pairs['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace_pairs);
    }

    /**
     * Parses Throwable object to string
     * @param \Throwable $thrown
     * @return string
     */
    protected function parseThrowable(\Throwable $thrown): string
    {
        $file = substr($thrown->getFile(), strpos($thrown->getFile(), $this->root) + strlen($this->root)); 
        $message = preg_replace("~class\@anonymous[^\s\'\"\,]*~", 'class@anonymous', $thrown->getMessage());
        return "[{$thrown->getCode()}] `$file:{$thrown->getLine()}` $message";
    }
}