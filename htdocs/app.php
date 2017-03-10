<?php

require("../vendor/autoload.php");

$config = new Gideon\Handler\Config('debug');
//$config->showDebugDetails();
?> 
<a href="<?=$config->get('UPLOAD')?>">upload directory</a>

<?php

$request = new Gideon\Http\Request($config);
//$request = new Gideon\Http\Request($config, 'index/simple/2');
//$request->showDebugDetails();

$router = new Gideon\Router\FastRouter($config);
$router->addRoute('users/:id', function(int $id) { echo "<p>User: $id</p>"; });
$router->addRoute('test/:nazwa_zmiennej', function($d) { echo "<p>Test: $d</p>"; })
    ->where(['nazwa_zmiennej' => '[a-zA-Z_]{3,5}']);
$router->addRoute('static/:any');
$router->addRoute('lol/:any/:id/mocart');
$router->addRoute('index/simple/:cst')->where(['cst' => '\d+']);
//$router->prepareAll();
//$router->showDebugDetails();

$route = $router->dispatch($request);//->showDebugDetails();
if(!$route->empty())
    $route->handler()($route->map($request)[0]);
else echo "<p>Empty Route</p>";

$router2 = new Gideon\Router\LoopRouter($config);
$router2->addRoute('users/:id', function(int $id) { echo "<p>User: $id</p>"; });
$router2->addRoute('test/:nazwa_zmiennej', function($d) { echo "<p>Test: $d</p>"; })
    ->where(['nazwa_zmiennej' => '[a-zA-Z_]{3,5}']);
$router2->addRoute('static/:any');
$router2->addRoute('lol/:any/:id/mocart');
$router2->addRoute('index/simple/:cst')->where(['cst' => '\d+']);
//$router2->prepareAll();
//$router2->showDebugDetails();

$route2 = $router2->dispatch($request);//->showDebugDetails();
if(!$route2->empty())
    $route2->handler()($route->map($request)[0]);
else echo "<p>Empty Route 2</p>";