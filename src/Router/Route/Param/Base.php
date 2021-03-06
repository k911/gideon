<?php
namespace Gideon\Router\Route\Param;

use Gideon\Router\Route\Param;

abstract class Base implements Param
{
    /**
     * @var string $name
     */
    public $name;

    /**
     * @var string  $value
     */
    public $value;

    /**
     * @var bool    $volatile
     */
    public $volatile;

    public function __construct(string $value)
    {
        $this->value;
    }
};
