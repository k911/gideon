<?php
declare(strict_types=1);

namespace Gideon\Filesystem;

use Gideon\Filesystem as FilesystemInterface;

abstract class Filesystem implements FilesystemInterface
{
    private static $userInfo;
    private $path;
    private $perms;
    private $owner;

    private function fileperms(string $path): string
    {
        return self::filterPermissions(fileperms($path) & 0777);
    }

    private function mov(string $old, string $new) {
        if(!file_exists($old)) {
            throw new IOException('Cannot move/rename file.',$old,404);
        }

        if(rename($old, $new) === false ){
            throw new AccessDeniedException('Unexpected error during move/rename file.',$old);
        }
    }

    private function chmod(string $path, string $mode)
    {
        $old = umask(0);
        $moded = @chmod($path, octdec($mode));
        umask($old);

        if (!$moded) {
            $perms = $this->fileperms($path);
            throw new IOException("Unexpected error during changing permissions from $perms to $mode", $path);
        }
    }

    protected function mkdirs(string $path, string $perms = '0755')
    {
        if (!file_exists($path)) {
            $old = umask(0);
            $created = @mkdir($path, octdec($perms), true);
            umask($old);

            if (!$created) {
                throw new AccessDeniedException("Unable to recursively create path with permissions: `$perms`", $path);
            }
        } elseif (is_file($path)) {
            throw new IOException('Given path is already an existing file', $path);
        }
    }

    protected function touch(string $path, int $modifiedAt = null, int $accessedAt = null)
    {
        $this->mkdirs(dirname($path));
        if(is_null($modifiedAt)) {
            $modifiedAt = time();
        }
        if (touch($path, $modifiedAt, $accessedAt ?? $modifiedAt) === false) {
            throw new AccessDeniedException('Unable to touch', $path);
        }
    }

    /**
     * @param int|string $perms
     * @return string
     */
    public static function filterPermissions($perms): string
    {
        if (is_int($perms) && $perms <= 511) {
            $perms = decoct($perms);
        }

        if (strlen($perms) === 3) {
            $perms = "0$perms";
        }
        return $perms;
    }

    public static function filterPath(string $path): string
    {
        $path = str_replace(self::DIRECTORY_SEPARATOR_INV, DIRECTORY_SEPARATOR, $path);
        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Opens an existing file or directory
     * @param string $path
     * @return File|Directory
     * @throws IOException
     */
    public static function open(string $path): FilesystemInterface
    {
        if(file_exists($path)) {
            return (is_file($path)) ? new File($path) : new Directory($path);
        } else {
            throw new IOException('Cannot open file.', $path, 404);
        }
    }

    public static function getCurrentUser(): array
    {
        if (isset(self::$userInfo))
            return self::$userInfo;

        ['name' => $user, 'gid' => $gid] = posix_getpwuid(posix_getuid());
        ['name' => $group] = posix_getgrgid($gid);
        return self::$userInfo = [$user, $group];
    }

    /**
     * @throws FilesystemException
     * @return Directory
     */
    public function getParent(): Directory
    {
        return new Directory(dirname($this->path));
    }

    public function isWritable(): bool
    {
        if (!$this->exists()) {
            throw new IOException('Cannot get information about access to write.', $this->path, 404);
        }

        return is_writable($this->path);
    }

    public function isReadable(): bool
    {
        if (!$this->exists()) {
            throw new IOException('Cannot get information about access to read.', $this->path, 404);
        }

        return is_readable($this->path);
    }

    public function isExecutable(): bool
    {
        if (!$this->exists()) {
            throw new IOException('Cannot get information about access to execute.', $this->path, 404);
        }

        return is_executable($this->path);
    }

    public function exists(): bool
    {
        return file_exists($this->path);
    }

    public function __construct(string $path)
    {
        $this->path = self::filterPath($path);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function rename(string $newName, int $modifyTime = null) : FilesystemInterface {
        $newName = dirname($this->path) . DIRECTORY_SEPARATOR . $newName;
        if(file_exists($newName)) {
            $newName = basename($newName);
            throw new AccessDeniedException("Cannot rename file to `$newName`. File already exists.", $this->getPath());
        }
        $this->mov($this->getPath(), $newName);
        $this->path = $newName;
        if(isset($modifyTime)) {
            $this->touch($this->getPath(), $modifyTime);
        }
        return $this;
    }

    public function getPermissions(): string
    {
        if (isset($this->perms))
            return $this->perms;

        if (!$this->exists()) {
            throw new IOException('Cannot get file permissions.', $this->path, 404);
        }
        return $this->perms = $this->fileperms($this->path);
    }

    public function setPermissions(string $permissions): FilesystemInterface
    {
        $permissions = self::filterPermissions($permissions);
        if ($permissions !== $this->getPermissions()) {
            if ($this->getOwner() === self::getCurrentUser()[0]) {
                $this->chmod($this->path, $permissions);
                $this->perms = $permissions;
            } else {
                $actual = $this->getPermissions();
                throw new AccessDeniedException("Cannot modify file permissions from `$actual` to `$permissions`", $this->path);
            }
        }
        return $this;
    }

    public function getOwner(): string
    {
        if (isset($this->owner))
            return $this->owner;

        if (!$this->exists()) {
            throw new IOException('Cannot get information about file owner.', $this->path, 404);
        }

        $owner = fileowner($this->path);
        if ($owner === false) {
            throw new FilesystemException('Unexpected failure during getting information about file owner', $this->path, 500);
        }
        return $this->owner = posix_getpwuid($owner)['name'];
    }
}