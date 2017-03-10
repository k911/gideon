<?php
require("../vendor/autoload.php");

$config = new Gideon\Handler\Config('test');
Gideon\Debug\Logger::init($config);

var_dump(Gideon\Debug\Logger::log('sss', 'test'));
Gideon\Debug\Logger::init($config);
Gideon\Debug\Logger::init($config);
Gideon\Debug\Logger::init($config);