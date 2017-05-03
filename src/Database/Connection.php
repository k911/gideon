<?php
namespace Gideon\Database;

use Gideon\Handler\Config;
use Gideon\Handler\Error as ErrorHandler;

interface Connection
{
    /**
     * Provides database object interface (e.g. PDO, MongoDB)
     * @param string $function name wanted to execute on \PDO object
     * @param array  $arguments which $function() takes
     * @return mixed whatever returned by $function()
     */
    public function __call(string $function, array $arguments);

    /**
     * Close connection to database
     */
    public function close();

    /**
     * Connect to database | no throw
     * @param ErrorHandler
     * @return bool true on success | false otherwise
     */
    public function try_connect(ErrorHandler $handler): bool;

    /**
     * Connect to database
     * @throws \PDOException
     */
    public function connect(): self;
}
