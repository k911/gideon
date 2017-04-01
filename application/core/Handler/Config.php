<?php
namespace Gideon\Handler;

use Gideon\Debug;

/**
 * Config keys used:
 * - LOGGER_INIT_DEFAULT
 */

class Config extends Debug\Provider
{

    /**
     * @var string PATH directory where configuration files are stored
     * @var array $origin contains default configuration array
     * @var array $extension contains extensions to configuration array
     * @var bool $extended indicates on initialization of $extension
     */
    const PATH = __DIR__ . '/../../config/';
    private $origin;
    private $extension;
    private $extended = false;

    private function load(string $config)
    {
        $config = self::PATH . "config.$config.php";
        if (file_exists($config)) 
        {
            $this->origin = require $config;
        } else 
        {
            $config = substr($config, strrpos($config, '/'));
            throw new \Error("Config file '$config' not found.");
        }
    }

    public function extend(array $extension, bool $force_cleanup = false)
    {
        if (!$force_cleanup && $this->extended) 
        {
            foreach ($extension as $key => $value)
                $this->extension[$key] = $value;
        } 
        else 
        {
            $this->extension = $extension;
            $this->extended = true;
        }
    }

    public function get(string $key, bool $force_origin = false)
    {
        if (!$force_origin && $this->extended && isset($this->extension[$key]))
            return $this->extension[$key];

        elseif (isset($this->origin[$key])) 
            return $this->origin[$key];

        $this->logger()->warning("Key '$key' is not set.");
        return $key;
    }
    /**
     * Example usage:
     * $config = new Config(...);
     * echo $config('TEXT_TEST');
     *
     * @param array $keys array of arguments $keys => [$arg1, $arg2, $arg3]
     * @return mixed null, string or string[]
     */
    public function __invoke(...$keys)
    {
        $count = count($keys);
        if($count === 1 && is_string($keys[0]))
            return $this->get($keys[0]);
        elseif ($count > 1)
        {
            $result = [];
            foreach($keys as $key)
            {
                if(is_string($key))
                {
                    $result[] = $this->get($key);
                }
            }
            return $result;
        }
    }
    public function __get(string $key)
    {
        return $this->get($key);
    }

    public function has(string $key, bool $force_origin = false): bool
    {
        return (!$force_origin && $this->extended) ? isset($this->extension[$key]) : isset($this->origin[$key]);
    }
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    public function __construct(string $config, $extension = null)
    {
        $this->load($config);
        if (!empty($extension)) 
            $this->extend($extension);
        
        // Enable debug on everything
        if ($this->get('LOGGER_INIT_DEFAULT') === true)
        {
            Debug\Provider::initLogger($this);
                // TODO: initLogger should throw exception
        }
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return [
            'origin' => $this->origin, 
            'extension' => $this->extension
        ];
    }
}
