<?php
namespace Gideon\Cache;

use DateInterval;
use Gideon\Exception\Exception;
use Gideon\Filesystem\Directory;
use Gideon\Filesystem\FilesystemException;
use Gideon\Filesystem\IOException;
use Gideon\Config;
use Psr\SimpleCache\CacheInterface;

/**
 * Implementation of PSR-16 Simple Cache
 * Stored data type: serialize()
 *
 * Config keys used:
 * - CACHE_PATH
 * - CACHE_MODE_DIR
 * - CACHE_MODE_FILE
 * - CACHE_TTL_DEFAULT
 * - CACHE_HASH_DEFAULT
 */
class SimpleCache implements CacheInterface
{
    /**
     * @var Directory cache directory
     */
    private $dir;

    /**
     * @var string $perms file permissions (default = '0644')
     */
    private $perms;

    /**
     * @var int $ttl default cache files time to live - in seconds
     */
    private $ttl;

    /**
     * @var string $hash function used to generate cached filenames
     */
    private $hash;

    /**
     * @param string $key
     * @return bool vaild on true | false otherwise
     */
    protected function validate(string $key): bool
    {
        // Key cannot contain characters "{}()\/@:"
        return !(preg_match('~[\{\}\\\\\(\)\/\@\:]~', $key) === 1);
    }

    protected function fileNameFrom(string $key): string {
        if (!$this->validate($key)) {
            throw new InvalidArgumentException("Key value `$key` contains illegal characters.");
        }

        return hash($this->hash, $key);
    }

    protected function pathFrom(string $key): string
    {
        return $this->dir->getPath() . DIRECTORY_SEPARATOR . $this->fileNameFrom($key);
    }

    public function __construct(Config $config)
    {
        $this->dir = new Directory($config->get('CACHE_PATH'), $config->get('CACHE_MODE_DIR') ?? '0700');
        $this->perms = $config->get('CACHE_MODE_FILE') ?? '0600';
        $this->ttl = $config->get('CACHE_TTL_DEFAULT') ?? 10*365*86400;
        $this->hash = $config->get('CACHE_HASH_DEFAULT') ?? 'sha256';
    }

    public function get($key, $default = null)
    {
        $path = $this->pathFrom($key);

        if (!file_exists($path)) {
            return $default;
        }

        // Check expiration timestamp and delete cache if so
        $expires_at = filemtime($path);
        if (time() >= $expires_at) {
            unlink($path);
            return $default;
        }

        $result = file_get_contents($path);
        if (($result = unserialize($result)) === false) {
            return $default;
        }

        return $result;
    }

    public function set($key, $value, $ttl = null)
    {
        try {
            $file = $this->dir->makeFile(uniqid("temp_", true));
            $file->setPermissions($this->perms);

            // Set default ttl if null
            if (is_null($ttl)) {
                $ttl = $this->ttl;
            }

            // Compute expiration timestamp
            if (is_int($ttl)) {
                $expires_at = time() + $ttl;
            } elseif ($ttl instanceof DateInterval) {
                $expires_at = date_create_from_format("U", time())->add($ttl)->getTimestamp();
            } else {
                throw new InvalidArgumentException("Illegal TTL: `" . var_export($ttl, true) . "`");
            }

            // Store data
            $file->put(serialize($value))->rename($this->fileNameFrom($key), $expires_at);
            return true;

        } catch (Exception $ex) {
            // Remove temp file upon error
            if(isset($file)) {
                $file->delete();
            }
        }

        return false;
    }

    public function delete($key)
    {
        unlink($this->pathFrom($key));
    }

    public function clear()
    {
        $this->dir->clear();
    }

    public function getMultiple($keys, $default = null)
    {
        if (!is_array($keys) && !($keys instanceof Traversable)) {
            throw new InvalidArgumentException('$keys has to be either array or Traversable.');
        }

        $result = [];
        foreach ($keys as $key) {
            $result[] = $this->get($key, $default);
        }
        return $result;
    }

    /**
     * @param array $data key => string valid key name
     * @return array key => same as key from $data, value => boolean (success or fail)
     */
    public function setMultiple($data, $ttl = null)
    {
        if (!is_array($data) && !($data instanceof Traversable)) {
            throw new InvalidArgumentException('$data has to be either array or Traversable.');
        }

        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = $this->set($key, $value, $ttl);
        }
        return $result;
    }

    public function deleteMultiple($keys)
    {
        if (!is_array($keys) && !($keys instanceof Traversable)) {
            throw new InvalidArgumentException('$data has to be either array or Traversable.');
        }

        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    public function has($key)
    {
        return file_exists($this->pathFrom($key));
    }
}
