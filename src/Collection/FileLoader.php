<?php
declare(strict_types=1);

namespace Gideon\Collection;

use Gideon\Collection;
use Gideon\Debug\Provider as Debug;
use Gideon\Filesystem\IOException;

class FileLoader extends Debug implements Collection
{

    /**
     * Container for values loaded via loadFromFile()
     * @var array
     */
    protected $data;

    /**
     * Container for additional values added after creation of this object
     * @var array
     */
    protected $extension;

    /**
     * Function used to load from file during construct
     * @param string $name symbolic name of required file
     * @throws IOException
     */
    protected function loadFromFile(string $name): array
    {
        $path = $this->createPathFromName($name);
        if (!file_exists($path)) {
            throw new IOException("File `$name` doesn't exist.", $path);
        }

        return require $path;
    }

    /**
     * Creates valid path to the requested php file from symbolic name
     * @param string $name symbolic name of requested file
     * @return string path to the valid file
     */
    protected function createPathFromName(string $name): string
    {
        return $name . '.php';
    }

    public function findOne(string $key)
    {
        $result = $this->extension[$key] ?? $this->data[$key] ?? null;
        if(is_null($result))
            $this->getLogger()->warning("Not found `$key` in container.");
        return $result;
    }

    public function findMultiple(array $keys): array
    {
        $results = [];
        foreach ($keys as $key) {
            $results[] = $this->findOne($key);
        }
        return $results;
    }

    public function __invoke(...$keys)
    {
        $count = count($keys);
        if ($count === 1) {
            return $this->findOne($keys[0]);
        } elseif ($count > 1) {
            return $this->findMultiple($keys);
        }
    }

    public function __get(string $key)
    {
        return $this->findOne($key) ?? $key;
    }

    public function has(string $key): bool
    {
        return (isset($this->extension[$key]) || isset($this->data[$key]));
    }

    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * Checks wheter container was extended since object construction
     * @return bool
     */
    public function isExtended(): bool
    {
        return !empty($this->extension);
    }

    /**
     * Adds data to container (covers original one if the same key)
     * @param array $extension
     * @return void
     */
    public function extend(array $extension)
    {
        if (empty($this->extension)) {
            $this->extension = $extension;
        } else {
            foreach ($extension as $key => $value) {
                $this->extension[$key] = $value;
            }
        }
    }

    /**
     * Removes values added via extend
     * @override
     * @return Collection
     */
    public function clear(): Collection
    {
        $this->extension = [];
        return $this;
    }

    /**
     * Object construction
     * @param string $name symbolic name of requested file
     * @param array $extension add to container some values overriding default ones from imported file
     */
    public function __construct(string $name, array $extension = null)
    {
        $this->data = $this->loadFromFile($name);
        $this->extension = $extension ?? [];
    }

    public function count(): int
    {
        return count($this->data) + count($this->extension);
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return [
            'data' => $this->data,
            'extension' => $this->extension
        ];
    }
}
