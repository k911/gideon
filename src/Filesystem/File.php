<?php
declare(strict_types=1);

namespace Gideon\Filesystem;

use Gideon\Filesystem;
use Gideon\Filesystem\Filesystem as Base;

class File extends Base
{

    public function __construct($path, int $modifiedAt = null, int $accessedAt = null)
    {
        parent::__construct($path);
        if(isset($modifiedAt) || isset($accessedAt)) {
            $this->touch($this->getPath(), $modifiedAt, $accessedAt);
        }
    }

    /**
     * Clear content of file, if it does not exists, file will be created
     * @return File
     * @throws AccessDeniedException
     */
    public function clear(): File {
        return $this->put('');
    }

    /**
     * Creates self if not exists
     * @return File
     */
    public function create(): File
    {
        $this->touch($this->getPath());
        return $this;
    }

    /**
     * Puts raw data into a file
     * @param $data
     * @param int $flags
     * @return File
     * @throws AccessDeniedException
     */
    public function put($data, int $flags = FILE_BINARY): File {
        if(file_put_contents($this->getPath(), $data, $flags) === false) {
            throw new AccessDeniedException('Cannot put data into a file.', $this->getPath());
        }
        return $this;
    }

    /**
     * Puts raw data into a file
     * @param $data
     * @return File
     * @throws AccessDeniedException
     */
    public function append(string $data): File {
        return $this->put($data, FILE_APPEND);
    }

    /**
     * @return Filesystem instance
     * @throws FilesystemException
     */
    public function delete(): File
    {
        if (!@unlink($this->getPath())) {
            throw new AccessDeniedException('Unable to delete a file', $this->getPath());
        }
        return $this;
    }
}