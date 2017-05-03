<?php
declare(strict_types=1);

namespace Gideon\Exception;

interface Any
{

    /**
     * @see Psr\Log\LogLevel
     * @return string
     */
    public function getLogLevel(): string;

    /**
     * Get custom getters names
     * E.g.: for getter getCamelCase() put down in array: camelCase
     * @return string[]
     */
    public function getGetters(): array;
}
