<?php
declare(strict_types=1);

namespace Gideon\Filesystem;

use Gideon\Exception\Exception;
use Throwable;

class FilesystemException extends Exception
{

    /**
     * @var string $path to file which caused IOException
     */
    private $path;

    /**
     * @var bool[] permissions
     */
    private $permissions;

    /**
     * @param string $message
     * @param string $path to error file/directory
     * @param int $code
     * @param Throwable $previous
     */
    public function __construct(string $message, string $path, int $code = 500, Throwable $previous = null)
    {
        $this->permissions['readable'] = is_readable($path);
        $this->permissions['writable'] = is_writable($path);
        $this->permissions['executable'] = is_executable($path);
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return realpath($this->path);
    }

    /**
     * @return string
     */
    public function getCurrentUser(): string
    {
        [$user, $group] = Filesystem::getCurrentUser();
        return "$user:$group";
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->permissions['writable'];
    }

    /**
     * @return bool
     */
    public function isReadable(): bool
    {
        return $this->permissions['writable'];
    }

    /**
     * @return bool
     */
    public function isExecutable(): bool
    {
        return $this->permissions['writable'];
    }

    /**
     * Permissions for current user
     * @return string r-w-e (read, write, execute)
     */
    public function getPermissions(): string
    {
        $perms = '';
        foreach ($this->permissions as $k => $v) {
            $perms .= ($v ? $k{1} : '-') . '-';
        }
        return rtrim($perms, '-');
    }


    public function getGetters(): array
    {
        return array_merge(parent::getGetters(), ['path', 'currentUser', 'permissions']);
    }
}
