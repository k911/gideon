<?php
require("../vendor/autoload.php");
$config = new Gideon\Handler\Config('debug', ['LOGGER_FILE' => __DIR__ . '/../application/log/app2.log']);
$cache = new Gideon\Cache\SimpleCache($config);
$cache->clear();
if(!$cache->has('ROUTER'))
{
    $router = new Gideon\Router\FastRouter($config);
    $router->addRoute('app2.php/user/:id', ['User', 'index'], 'GET');
    $router->prepareAll();
    $cache->set('ROUTER', $router, 60*3600);
} 
else 
{
    $router = $cache->get('ROUTER');
}
$app = new Gideon\Application($config, $router);
$app->run();
//$app->showDebugDetails();
$app->render();