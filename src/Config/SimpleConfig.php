<?php
declare(strict_types=1);

namespace Gideon\Config;

use Gideon\Config;
use Gideon\Debug\Provider;
use Gideon\Collection\FileLoader;
use Gideon\Exception\SystemFailure;

/**
 * Config keys used:
 * - LOGGER_INIT_DEFAULT
 */

class SimpleConfig extends FileLoader implements Config
{

    /**
     * directory where configuration files are stored
     * @var string
     */
    private $path;

    /**
     * Alias for function findOne
     * @param string $key
     * @return mixed
     */
    public function get(string $key) {
        return $this->findOne($key);
    }

    /**
     * @override
     */
    protected function createPathFromName(string $name): string
    {
        return $this->path . "config.$name.php";
    }

    /**
     * This function is called when not provided path in constructor
     * Used for easy development | Can override
     * @return string
     */
    protected function getDefaultPath(): string
    {
        return realpath(__DIR__ . DIRECTORY_SEPARATOR . 'defaults') . DIRECTORY_SEPARATOR;
    }

    public function __construct(string $config, string $path = null, array $extension = null)
    {
        $this->path = $path ?? $this->getDefaultPath();

        parent::__construct($config, $extension);

        // Enable debug on everything
        if ($this->get('LOGGER_INIT_DEFAULT') === true) {
            if (!Provider::initDebugProvider($this)) {
                throw new SystemFailure('Logger cannot be intialized, therefore system is not debugable');
            }
        }
    }

    /**
     * @override
     */
    public function getDebugProperties(): array
    {
        return array_merge(parent::getDebugProperties(), [
            'path' => $this->path,
        ]);
    }
}
