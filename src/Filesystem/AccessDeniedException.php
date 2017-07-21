<?php
declare(strict_types=1);

namespace Gideon\Filesystem;

use Throwable;

class AccessDeniedException extends FilesystemException
{
    /**
     * @var string file/directory owner
     */
    private $owner;

    /**
     * @var string current user name
     */
    private $user;

    /**
     * @var string current user group
     */
    private $group;

    public function __construct($message, $path, $code = 500, Throwable $previous = null)
    {
        if($message{-1} !== '.') {
            $message .= '.';
        }
        [$this->user, $this->group] = Filesystem::getCurrentUser();
        $message .= " User: [{$this->group}:{$this->user}].";
        if(file_exists($path)) {
            $filesystem = Filesystem::open($path);
            $this->owner = $filesystem->getOwner();
            $message .= " Owner: `{$this->owner}`.";
        }


        parent::__construct($message, $path, $code, $previous);
    }
}