<?php

namespace Gideon\Renderer;

use Gideon\Handler\Config;
use Gideon\Handler\Locale;

interface Response 
{

    /**
     * Sets data that is used by this object
     */
    public function setHandler($handler): self;

    /**
     * Sets http response code that is returned by renderer
     */
    public function setCode(int $code): self;

    /**
     * Sets response's http content-type 
     */
    public function setType(string $type): self;

    /**
     * Add variable to rendering scope
     * @param   string    $name 
     * @param   mixed     $value
     * @return  Gideon\Renderer\Response
     */
    public function bindParam(string $name, $value): self;

    /**
     * Add multiple variables to rendering scope
     * @param   array    $data  key => name, value => data
     * @return  Gideon\Renderer\Response
     */
    public function bindParams(array $data): self;

    /**
     * Verifies propeties with configs and fixes them if possible
     * @param \Gideon\Handler\Config $config
     */
    public function setup(Config $config);

    /**
     * Rendering method; outputs ready response to main buffer
     * @param \Gideon\Handler\Config $config 
     * @param \Gideon\Handler\Locale $locale
     * @param \stdClass             $document general informations provided by application (not from controller)
     */
    public function render(Config $config, Locale $locale, \stdClass $document);

    /**
     * Magic method get
     * @param string $key
     * @return mixed
     */
    public function __get(string $key);

    /**
     * Magic method isset
     * @param string $key
     * @return bool
     */
    public function __isset(string $key);

}