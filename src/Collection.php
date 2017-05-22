<?php
declare(strict_types=1);

namespace Gideon;

use Countable;
use Gideon\Exception\IOException;

interface Collection extends Countable
{

    /**
     * Gets value for given key
     * @param string $key
     * @return mixed|null
     */
    public function findOne(string $key);

    /**
     * Gets values for given keys
     * @example usage: [$item1, $item2] = $container->findMultiple(['KEY1', 'KEY2']);
     * @param string[] $keys
     * @return array
     */
    public function findMultiple(array $keys): array;

    /**
     * Gets values for given keys
     * @example usage: $item = $container('KEY');
     * @example usage: [$item1, $item2] = $container('KEY1', 'KEY2');
     * @param string ...$keys
     * @return mixed|array if one key given it simply returns it value, array otherwise
     */
    public function __invoke(...$keys);

    /**
     * Gets value for given key, but never returns null
     * @return mixed
     */
    public function __get(string $key);

    /**
     * Checks wheter value for key is set in container
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Alias to has()
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool;

    /**
     * Removes data keeped in container
     * @return void
     */
    public function clear(): self;

}
