<?php

namespace Gideon\Http;

use stdClass;
use Gideon\Config;
use Gideon\Locale;

interface Response
{
    /**
     * Sets data that is used by this object
     * @return Gideon\Http\Response
     */
    public function setHandler($handler): self;

    /**
     * Sets http response code that is returned by renderer
     * @return Gideon\Http\Response
     */
    public function setCode(int $code): self;

    /**
     * Sets response's http content-type
     * @return Gideon\Http\Response
     */
    public function setType(string $type): self;

    /**
     * Binds value of variable to rendering scope
     * Uses referece.
     * @param string $name
     * @param mixed $value
     * @return Gideon\Http\Response
     */
    public function bindParam(string $name, &$value): self;

    /**
     * Add variable to rendering scope
     * @param string $name
     * @param mixed $value
     * @return Gideon\Http\Response
     */
    public function setParam(string $name, $value): self;

    /**
     * Add multiple variables to rendering scope
     * @param   array    $data  key => name, value => data
     * @return  Gideon\Http\Response
     */
    public function setParams(array $data): self;

    /**
     * Verifies propeties with configs and fixes them if possible
     * @param \Gideon\Config $config
     * @return void
     */
    public function setup(Config $config);

    /**
     * Renders response body to main buffer
     * @param \Gideon\Config $config
     * @param \Gideon\Locale $locale
     * @param stdClass $document general informations provided by application (not from controller)
     * @return void
     */
    public function render(Config $config, Locale $locale = null, stdClass $document = null);

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
