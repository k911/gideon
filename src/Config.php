<?php
declare(strict_types=1);

namespace Gideon;

interface Config extends Collection
{
    /**
     * Alias for function findOne
     * @param string $key
     * @return mixed
     */
    public function get(string $key);
}
