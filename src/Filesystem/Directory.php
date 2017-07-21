<?php
declare(strict_types=1);

namespace Gideon\Filesystem;

use Countable;
use Gideon\Filesystem\Filesystem as Base;
use Gideon\Filesystem;
use IteratorAggregate;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class Directory extends Base implements IteratorAggregate, Countable
{
    public function __construct(string $path, string $permissions = null)
    {
        parent::__construct($path);
        if (isset($permissions)) {
            $this->mkdirs($this->getPath(), self::filterPermissions($permissions));
            $this->setPermissions($permissions);
        }
    }

    /**
     * @param string $relative path
     * @return bool
     */
    public function has(string $relative)
    {
        return file_exists($this->getPath() . DIRECTORY_SEPARATOR . $relative);
    }

    public function delete(): Filesystem
    {
        $this->clear();
        if (!@rmdir($this->getPath())) {
            throw new AccessDeniedException('Unable to delete a root directory', $this->getPath());
        }
        return $this;
    }

    public function clear(): self
    {
        foreach ($this as $file) {
            $path = $file->getPathname();
            $removed = ($file->isDir()) ? @rmdir($path) : @unlink($path);
            if (!$removed) {
                throw new AccessDeniedException('Unable to delete a file recursively', $path);
            }
        }
        return $this;
    }

    /**
     * Creates self if not exists
     * @return Filesystem
     */
    public function create(): Directory
    {
        $this->mkdirs($this->getPath());
        return $this;
    }

    /**
     * Creates a file if not exists
     * @param string $file
     * @param int|null $createdAt timestamp
     * @param int|null $modifiedAt timestamp
     * @return File
     */
    public function makeFile(string $file, int $modifiedAt = null, int $accessedAt = null): File
    {
        return new File($this->getPath() . DIRECTORY_SEPARATOR . $file, $modifiedAt ?? time(), $accessedAt);
    }

    /**
     * Gets relative file
     * @param string $file
     * @return File
     */
    public function getFile(string $file): File
    {
        return new File($this->getPath() . DIRECTORY_SEPARATOR . $file);
    }

    public function getIterator()
    {
        return new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getPath(), FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    }

    public function count(): int
    {
        if (!$this->exists()) {
            return 0;
        }
        return iterator_count(new FilesystemIterator($this->getPath(), FilesystemIterator::SKIP_DOTS));
    }

    public function isEmpty()
    {
        return !$this->exists() || !(new FilesystemIterator($this->getPath()))->valid();
    }
}