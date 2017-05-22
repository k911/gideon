<?php
namespace Gideon\Router\Route\Param;

class ArrayRouteParam extends Base
{
    /**
     * @var string $regex
     */
    public $regex;

    public function __construct(string $value)
    {
        $value = trim($value);

        // Determine variable basing if first character is ':'
        if ($this->volatile = (mb_substr($value, 0, 1) === ':')) {
             // Check for custom defined regex pattern
            if ($this->regex = (preg_match('/^:{.+}$/', $value)) === 1) {
                $this->value = trim(mb_substr($value, 2, -1));
            } else {
                $this->name = trim(mb_substr($value, 1));
            }
        } else {
            $this->value = $value;
        }
    }
}
