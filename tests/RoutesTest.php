<?php

use PHPUnit\Framework\TestCase;
use Gideon\Router;
use Gideon\Router\Route;
use Gideon\Handler\Config;
use Gideon\Handler\Group\ArrayGroup;
use Gideon\Http\Request;

class RoutesTest extends TestCase
{
    private $config;
    private $routers;

    public function __construct()
    {
        $this->config = new Config('test');
    }

    public function setUp()
    {
        $routers = (new ArrayGroup())->add(new Router\FastRouter($this->config), new Router\LoopRouter($this->config));
        foreach($routers as $router)
            $this->assertEquals(true, $router instanceof Gideon\Router);

        $this->routers = $routers;
    }

    /**public function testRouteAddidtion()
    {

    }*/

    public function testSimpleAddRoutes()
    {
        $results = $this->routers->isEmpty();
        foreach($results as $result)
        {
            $this->assertEquals(true, $result);
        }

        // test route integrity
        $r = 10;
        $id = 99; $any = "something";
        $false_numeric = 'la_NG';
        for($i = 0; $i < $r; ++$i)
        {
            $route_txt = "test/$i/:id/:any/static/:numeric";
            $matching_req = new Request($this->config, "test/$i/$id/$any/static/$false_numeric", 'GET');
            $uri = $matching_req->uri();

            $routes = ($this->routers->addRoute($route_txt, null, 'GET'))->where(['numeric' => '[a-z]{2}_[A-Z]{2}']);
            foreach($routes as $route)
            {
                // test custom replacements
                $this->assertEquals(count($matching_req), count($route));
                $this->assertEquals([$id, $any, $false_numeric], $route->map($matching_req));

                // must match to request
                $regex = $route->toPattern($this->config->get('ROUTER_REPLACEMENTS_DEFAULT'));
                $this->assertSame(1, preg_match('~^' . $regex . '$~', $uri));
            }
        }
    }


}
