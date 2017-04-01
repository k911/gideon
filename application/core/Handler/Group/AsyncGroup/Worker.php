<?php
namespace Gideon\Handler\Group\AsyncGroup;

class Worker extends \Thread
{
    protected $work;
    protected $payload;
    public $result;

    public function __construct(callable $work, array $paylaod = null)
    {
        $this->work = $work;
        $this->payload = $payload;
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $this->result = call_user_func_array($this->work, $this->payload);
    }
}