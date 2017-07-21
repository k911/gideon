<?php

namespace Gideon\Debug;

use Gideon\Filesystem\AccessDeniedException;
use Gideon\Filesystem\Directory;
use Gideon\Filesystem\IOException;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * PSR-3 Logger implementation
 */
class Logger extends AbstractLogger
{
    /**
     * @var string HEADING
     */
    const HEADING = "[Timestamp] [Type] [Prefix] [Message]";

    /**
     * @var string $logFile path to writable/creatable file
     */
    protected $logFile;

    /**
     * @var string $prefix to write in log line after log level
     */
    protected $prefix;

    /**
     * Logger constructor.
     * @param string $dir
     * @param string $file
     * @throws IOException
     */
    public function __construct(string $dir, string $file = 'log')
    {
        $this->logFile = (new Directory($dir, '0755'))->getFile($file);
        if (!$this->logFile->exists()) {
            $this->logFile->create()->setPermissions('0644');
            $this->writeLine(self::HEADING);
        }
    }

    /**
     * Write log line
     * Tries to save it in utf-8 encoding using this hack:
     * @link http://stackoverflow.com/questions/7979567/php-convert-any-string-to-utf-8-without-knowing-the-original-character-set-or
     * @param string $line
     * @throws AccessDeniedException
     */
    protected function writeLine(string $line)
    {
        // Convert to UTF-8 and remove newline from endings
        $line = trim($line);
        $line = iconv(mb_detect_encoding($line, mb_detect_order(), true), "UTF-8", $line);

        if (file_put_contents($this->logFile->getPath(), $line . PHP_EOL, FILE_APPEND) === false) {
            throw new AccessDeniedException('Cannot write line to a file.', $this->logFile->getPath());
        }
    }

    /**
     * Changes prefix and returns itself
     * @param string $prefix
     * @return \Gideon\Debug\Logger
     */
    public function withPrefix(string $prefix): Logger
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Clear logFile
     * @throws IOException
     * @return Logger
     */
    public function clear(): Logger
    {
        $this->logFile->clear();
        $this->writeLine(self::HEADING);
        return $this;
    }

    /**
     * Main log function
     * @param string $level
     * @see \Psr\Log\LogLevel
     * @param string|\Throwable|\Serializable $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        // Replace templates
        if (!empty($context))
            $message = $this->interpolate($message, $context);

        // Add prefix
        if (!empty($this->prefix))
            $message = "[{$this->prefix}] $message";

        // Create and save line to log file
        $timestamp = time();
        $level = ($level === LogLevel::ERROR || $level === LogLevel::CRITICAL || $level === LogLevel::ALERT || $level === LogLevel::EMERGENCY) ?
            "! $level" : (
            ($level === LogLevel::DEBUG || $level === LogLevel::INFO) ?
                "  $level" :
                "- $level");
        $this->writeLine("$timestamp $level: $message");
    }

    /**
     * Interpolates context values into the message placeholders.
     * @param string $message with optional templates: {{template_name}}
     * @param array $context context key => replacement
     * @return string interpolated output
     */
    protected function interpolate(string $message, array $context = []): string
    {
        // build a replacement array with braces around the context keys
        $replace_pairs = [];
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace_pairs['{{' . $key . '}}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace_pairs);
    }
}
