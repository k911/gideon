<?php
namespace Gideon;

interface Debug 
{
    /**
     * Saves log line [timestamp, id, message] to file using Gideon\Debug\Logger
     * @param string $what message to store
     * @return bool true when successfully saved | false otherwise
     */
    public function log(string $what): bool;

    /**
     * Return array of debuged object propeties
     * @return array
     */
    public function getDebugDetails(): array;

    /**
     * Does prints to default buffer output of getDetails()
     */
    public function showDebugDetails();
}