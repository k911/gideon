<?php
namespace Gideon\Renderer\Response;

use Gideon\Debug\Base as Debug;
use Gideon\Renderer\Response;
use Gideon\Handler\Config;
use Gideon\Handler\Locale;

abstract class Base extends Debug implements Response
{
    /**
     * @var mixed   $handler    a unknown type data (e.g. filename)
     */
    protected $handler;
    
    /**
     * @var string      $type       http content-type value
     */
    protected $type;
    
    /** 
     * @var int         $code       http response code
     */
    protected $code;
    
    /**
     * @var \stdClass   $params     variables used in rendering process
     */
    protected $params;

    abstract public function setup(Config $config);
    abstract public function render(Config $config, Locale $locale, \stdClass $document);

    public function setHandler($handler): Response 
    {
        $this->handler = $handler;
        return $this;
    }
    public function setCode(int $code): Response 
    {
        $this->code = $code;
        return $this;
    }
    public function setType(string $type): Response 
    {
        $this->type = $type;
        return $this;
    }

    public function bindParam(string $param, $value): Response 
    {
        $this->params->{$param} = $value;
        return $this;
    }
    public function bindParams(array $params): Response 
    {
        foreach($params as $key => $value)
            $this->params->{$key} = $value;
        return $this;
    }

    public function __construct($handler = null, int $code = null, string $type = null)
    {
        $this->params = new \stdClass();

        if(isset($handler))
            $this->setHandler($handler);
        if(isset($code))
            $this->setCode($code);
        if(isset($type))
            $this->setType($type);
    }

    public function __get(string $key)
    {
        if(isset($this->{$key}))
            return $this->$key;
    }

    public function __isset(string $key): bool
    {
        return isset($this->{$key});
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        return [
            'code' => $this->code,
            'type' => $this->type,
            'handler' => $this->handler,
            'params' => $this->params
        ];
    }


}