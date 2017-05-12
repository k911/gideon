<?php
namespace Gideon\Application;

use Gideon\Debug;
use Gideon\Exception\IOException;
use Gideon\Application\SystemFailure;

/**
 * Config keys used:
 * - LOGGER_INIT_DEFAULT
 */

class Config extends Debug\Provider
{

    /**
     * @var string $path directory where configuration files are stored
     * @var array $origin contains default configuration array
     * @var array $extension contains extensions to configuration array
     * @var bool $extended indicates on initialization of $extension
     */
    private $path;
    private $origin;
    private $extension;
    private $extended = false;

    /**
     * @param string $config name of config
     * @throws IOException
     */
    private function load(string $config = 'default')
    {
        $configPath = $this->path . "config.$config.php";
        if (!file_exists($configPath)) {
            throw new IOException("Config file `$config` not found.", $configPath);
        }

        $this->origin = require $configPath;
    }

    /**
     * @param array $extension
     * @param bool $force_cleanup
     * @return self
     */
    public function extend(array $extension, bool $force_cleanup = false)
    {
        if (!$force_cleanup && $this->extended) {
            foreach ($extension as $key => $value) {
                $this->extension[$key] = $value;
            }
        } else {
            $this->extension = $extension;
            $this->extended = true;
        }
        return $this;
    }

    /**
     * @param string $key
     * @param bool $force_origin don't use extended values
     * @return mixed|null
     */
    public function get(string $key, bool $force_origin = false)
    {
        if (!$force_origin && $this->extended && isset($this->extension[$key])) {
            return $this->extension[$key];
        } elseif (isset($this->origin[$key])) {
            return $this->origin[$key];
        }

        $this->getLogger()->warning("Key '$key' is not set.");
    }

    /**
     * Example usage:
     * $config = new Config(...);
     * echo $config('TEXT_TEST');
     *
     * @param array $keys array of arguments $keys => [$arg1, $arg2, $arg3]
     * @return mixed|null
     */
    public function __invoke(...$keys)
    {
        $count = count($keys);
        if ($count === 1 && is_string($keys[0])) {
            return $this->get($keys[0]);
        } elseif ($count > 1) {
            $result = [];
            foreach ($keys as $key) {
                if (is_string($key)) {
                    $result[] = $this->get($key);
                }
            }
            return $result;
        }
    }

    /**
     * Calls get function but doesn't return null when not found
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->get($key) ?? $key;
    }

    public function has(string $key, bool $force_origin = false): bool
    {
        return (!$force_origin && $this->extended) ?
            isset($this->extension[$key]) :
            isset($this->origin[$key]);
    }
    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * This function is called when not provided path in constructor
     * Used for easy development | Can override
     * @return string
     */
    public function getDefaultPath(): string
    {
        return __DIR__ . '/../../application/config/';
    }

    public function __construct(string $config, string $path = null, array $extension = null)
    {
        if (is_null($path)) {
            $this->path = $this->getDefaultPath();
        }

        $this->load($config);
        if (!empty($extension)) {
            $this->extend($extension);
        }

        // Enable debug on everything
        if ($this->get('LOGGER_INIT_DEFAULT') === true) {
            if (!Debug\Provider::initDebugProvider($this)) {
                throw new SystemFailure('Logger cannot be intialized, therefore system is not debugable');
            }
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
