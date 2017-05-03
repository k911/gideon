<?php
namespace Gideon;

use Psr\Log\LoggerInterface as Logger;

interface Debug 
{
    /**
     * Getter of logger object instance compatible with PSR-3
     * @return Psr\Log\LoggerInterface
     */
    public function getLogger(): Logger;

    /**
     * Return array of debuged object propeties
     * @return array
     */
    public function getDebugDetails(): array;

    /**
     * Prints provided debug informations to default buffer
     * @return void
     */
    public function showDebugDetails();
}