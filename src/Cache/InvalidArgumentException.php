<?php
namespace Gideon\Cache;

use Gideon\Exception\InvalidArgumentException as Base;

class InvalidArgumentException extends Base implements \Psr\SimpleCache\InvalidArgumentException
{

}
