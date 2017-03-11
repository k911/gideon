<?php
namespace Gideon;

interface Debug 
{
    /**
     * Saves log line [timestamp, id, message] to file
     * @param string $what message to store
     * @return bool true when successfully saved | false otherwise
     */
    public function log(string $what): bool;

    /**
     * Saves log line [timestamp, throwable_name, its_details] to file
     * @param \Throwable $thrown
     * @return bool true when successfully saved | false otherwise
     */
    public function logException(\Throwable $thrown): bool;

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