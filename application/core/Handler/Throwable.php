<?php
namespace Gideon\Handler;

use Gideon\Debug\Base as Debug;
use Gideon\Handler\Config;

/**
 * Config keys used:
 * - THROWABLE_SESSION_ARRAY
 */

class Throwable extends Debug
{
    // Full static class
    private function __construct() {}
    
    /**
     * @static Throwable[] $Trash
     */
    private static $Trash = [];

    /**
     * @param string $name
     * @return string file path starting in root directory
     */
    private static function hideRootFrom(string $path): string 
    {
        $root = 'htdocs';
        return '...' . substr($path, strpos($path, $root) + strlen($root));  
    }

    private static function prepareTrace(array $traces): array 
    {
        foreach($traces as $index => $trace) 
        {
            if(isset($trace['file']))
                $traces[$index]['file'] = self::hideRootFrom($trace['file']);
        }
        return $traces;
    }

    private static function parse(\Throwable $thrown): array
    {
        $data['type'] = get_class($thrown);
        $data['code'] = $thrown->getCode();
        $data['message'] = $thrown->getMessage();
        $data['line'] = $thrown->getLine();
        $data['file'] = self::hideRootFrom($thrown->getFile());
        $data['traces'] = self::prepareTrace($thrown->getTrace());
        return $data;
    }

    public static function load(Config $config) 
    {
        $trashed = $_SESSION[$config->get('THROWABLE_SESSION_ARRAY')] ?? false;
        if($trashed !== false) 
        {
            unset($_SESSION[$config->get('THROWABLE_SESSION_ARRAY')]);
            return $trashed;
        } 
        self::log('There were NOT any throwables stored');
    }

    public static function store(Config $config) 
    {
        $parsed = self::get();
        $counter = 0;
        if(isset($parsed)) 
        {
            foreach($parsed as $index => $thrown) 
            {
                self::log('Stored.. ' . $thrown['type']);
                $_SESSION[$config->get('THROWABLE_SESSION_ARRAY')][] = $thrown;
                ++$counter;
            }
        }
        return $counter;
    }

    public static function empty(): bool
    {
        return empty(self::$Trash);
    }

    public static function get(): array 
    {
        $parsed = [];
        foreach(self::$Trash as $index => $thrown) 
        {
            unset(self::$Trash[$index]);
            do {
                $parsed[] = self::parse($thrown);
            } while ($thrown = $thrown->getPrevious());
        }
        return $parsed;
    }

    public static function catch(\Throwable $thrown) 
    {
        self::$Trash[] = $thrown;
    }

    public static function try(callable $closure, ...$args) 
    {
        $result = null;
        try 
        {
            $result = (isset($args)) ? call_user_func_array($closure, $args) : call_user_func($closure);
        } 
        catch (\Throwable $thrown) 
        {
            self::$Trash[] = $thrown;
        } 
        finally 
        {
            return $result;
        }
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return ['trash' => self::$Trash];
    }

}