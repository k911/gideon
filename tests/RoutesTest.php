<?php

use PHPUnit\Framework\TestCase;
use Gideon\Router;
use Gideon\Router\Route;
use Gideon\Handler\Config;
use Gideon\Handler\Group\UniformGroup as Group;
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
        $this->routers = new Group('Gideon\Router');
        $this->routers->add(new Router\FastRouter($this->config), new Router\LoopRouter($this->config));
    } 

    public function testSimpleAddRoutes()
    {
        $results = $this->routers->empty();
        foreach($results as $result)
        {
            $this->assertEquals(true, $result);
        }

        // test route integrity
        $r = 10;
        $id = 99; $any = "something";
        $cstom = 'la_NG';
        for($i = 0; $i < $r; ++$i)
        {
            $route_txt = "test/$i/:id/:any/static/:cstom";
            $matching_req = new Request($this->config, "test/$i/$id/$any/static/$cstom", 'GET');
            $uri = $matching_req->uri();

            $routes = $this->routers->addRoute($route_txt, null, 'GET');
            foreach($routes as $route)
            {
                // test custom replacements
                $route->where(['cstom' => '[a-z]{2}_[A-Z]{2}']);
                $this->assertEquals($matching_req->size(), $route->size());
                $this->assertEquals([$id, $any, $cstom], $route->map($matching_req));

                // must match to request
                $regex = $route->regex($this->config->get('ROUTER_REPLACEMENTS_DEFAULT'));
                $this->assertSame(1, preg_match('~^' . $regex . '$~', $uri));
            }
        }
    }


}