<?php
declare(strict_types=1);

namespace Gideon\Model;

use Gideon\Application\Config;
use Gideon\Handler\Error as ErrorHandler;
use Gideon\Database\Connection\MySQL;

class Article
{
    /**
     * @var MySQL $connection
     */
    private $connection;


    public function __construct(Config $config, ErrorHandler $errorHandler)
    {
        $connection = (new MySQL($config, [
            'username' => 'root',
            'dbname' => 'knit_backup',
            'charset' => 'latin1'
        ]))->connect();
    }
}
