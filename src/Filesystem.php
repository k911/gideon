<?php
declare(strict_types=1);

namespace Gideon;

use Gideon\Filesystem\Directory;
use Gideon\Filesystem\FilesystemException;

interface Filesystem
{

    /**
     * Opposite of DIRECTORY_SEPARATOR php constant
     */
    const DIRECTORY_SEPARATOR_INV = (DIRECTORY_SEPARATOR === '/') ? '\\' : '/';

    /**
     * Gets current user name and group name
     * @return string[] [$user, $group] names
     */
    public static function getCurrentUser(): array;

    /**
     * @param string $path
     * @return string
     */
    public static function filterPath(string $path): string;

    /**
     * @throws FilesystemException
     * @return Filesystem
     */
    public function getParent(): Directory;

    /**
     * @return string currently managed path
     */
    public function getPath(): string;

    /**
     * @return string four digit octal number
     */
    public function getPermissions(): string;

    /**
     * @param string $permissions octal number
     * @return Filesystem
     */
    public function setPermissions(string $permissions): self;

    /**
     * Creates self if not exists
     * @return Filesystem
     * @throws FilesystemException
     */
    public function create();

    /**
     * @return bool
     */
    public function exists(): bool;

    /**
     * @return string
     */
    public function getOwner(): string;

    /**
     * @return bool
     */
    public function isWritable(): bool;

    /**
     * @return bool
     */
    public function isReadable(): bool;

    /**
     * @return bool
     */
    public function isExecutable(): bool;

    /**
     * @return Filesystem instance
     * @throws FilesystemException
     */
    public function delete();

}