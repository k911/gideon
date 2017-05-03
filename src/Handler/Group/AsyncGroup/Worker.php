<?php
declare(strict_types=1);

namespace Gideon\Handler\Group\AsyncGroup;

use Thread;

class Worker extends Thread
{
    protected $work;
    protected $payload;
    public $result;

    public function __construct(callable $work, array $paylaod = null)
    {
        $this->work = $work;
        $this->noPayload = is_null($paylaod);
        $this->payload = $payload;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $this->result = ($this->noPayload) ? call_user_func($this->work) : call_user_func_array($this->work, $this->payload);
    }
}
