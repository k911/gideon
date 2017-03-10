<?php
namespace Gideon\Cache;

use Gideon\Application\IOException;
use Gideon\Handler\Config;
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
     * @var string  $path
     * @var string  $fmode file mode
     * @var int     $ttl default time to live - in seconds
     * @var string  $hash function used to generate cached filenames
     */
    private $path;
    private $fmode;
    private $ttl;
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

    protected function pathFrom(string $key): string
    {
        if(!$this->validate($key))
            throw new InvalidArgumentException("Key value `$key` contains illegal characters.");

        return $this->path . DIRECTORY_SEPARATOR . hash($this->hash, $key);
    }

    public function __construct(Config $config)
    {
        $path = $config->get('CACHE_PATH');
        $dmode = $config->isset('CACHE_MODE_DIR') ? $config->get('CACHE_MODE_DIR') 
            : 0777; // default
        
        // Verify path settings
        if(!file_exists($path))
        {
            $old = umask(0);
            if(!mkdir($path, $dmode, true))
                throw new IOException($path, "Cannot create dir");
            umask($old);
        }

        elseif(!is_dir($path))
            throw new IOException($path, "Not a directory");

        elseif((fileperms($path) & 0777) != $dmode)
        {
            if(!chmod($path))
                throw new IOException($path, "Cannot set dir mode $dmode");
        }

        $this->path = $path;
        $this->fmode = $config->get('CACHE_MODE_FILE');
        $this->ttl = $config->isset('CACHE_TTL_DEFAULT') ? $config->get('CACHE_TTL_DEFAULT') 
            : 10*365*86400; // aprox. 10 years
        $this->hash = $config->isset('CACHE_HASH_DEFAULT') ? $config->get('CACHE_HASH_DEFAULT')
            : 'sha256';

    }

    public function get($key, $default = null)
    {
        $path = $this->pathFrom($key);

        if(!file_exists($path))
        {
            return $default;
        }

        // Check expiration timestamp and delete cache if so
        $expires_at = filemtime($path);
        if(time() >= $expires_at)
        {
            unlink($path);
            return $default;
        }

        $result = file_get_contents($path);
        if(($result = unserialize($result)) === false)
        {
            return $default;
        }
            
        return $result;
    }

    public function set($key, $value, $ttl = null)
    {
        $dest = $this->pathFrom($key);
        $temp = $this->path . DIRECTORY_SEPARATOR . uniqid("temp_", true);

        // Set default ttl if null
        if(is_null($ttl))
            $ttl = $this->ttl;

        // Compute expiration timestamp
        if (is_int($ttl)) 
            $expires_at = time() + $ttl;
        elseif ($ttl instanceof DateInterval)
            $expires_at = date_create_from_format("U", time())->add($ttl)->getTimestamp();
        else 
            throw new InvalidArgumentException("Illegal TTL: `" . var_export($ttl, true) . "`");

        // Create temp file with serialized data
        if(file_put_contents($temp, serialize($value)) === false)
            return false;

        // Change file mod, create destination file and rename temp file
        if(chmod($temp, $this->fmode) !== false)
        {
            if(rename($temp, $dest) && touch($dest, $expires_at))
                return true;
        }

        // Remove temp file upon error
        unlink($temp);
        return false;
    }

    public function delete($key)
    {
        unlink($this->pathFrom($key));
    }

    public function clear()
    {
        array_map('unlink', glob($this->path . '*'));
        rmdir($this->path);
    }

    public function getMultiple($keys, $default = null)
    {
        if (!is_array($keys) && !($keys instanceof Traversable))
            throw new InvalidArgumentException('$keys has to be either array or Traversable.');

        $result = [];
        foreach($keys as $key)
        {
            $result[] = $this->get($keys, $default);
        }
        return $result;
    }

    /**
     * @param array $data key => string valid key name
     * @return array key => same as key from $data, value => boolean (success or fail)
     */
    public function setMultiple($data, $ttl = null)
    {
        if (!is_array($keys) && !($keys instanceof Traversable))
            throw new InvalidArgumentException('$data has to be either array or Traversable.');
        
        $result = [];
        foreach($data as $key => $value)
        {
            $result[$key] = $this->set($key, $value, $ttl);
        }
        return $result;
    }

    public function deleteMultiple($keys)
    {
        if (!is_array($keys) && !($keys instanceof Traversable))
            throw new InvalidArgumentException('$data has to be either array or Traversable.');
        
        foreach($keys as $key)
        {
            $this->delete($key);
        }
    }

    public function has($key)
    {
        return file_exists($this->pathFrom($key));
    }
}