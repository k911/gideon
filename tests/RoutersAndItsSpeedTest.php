<?php

use PHPUnit\Framework\TestCase;
use Gideon\Router;
use Gideon\Http\Request;
use Gideon\Handler\Config;
use Gideon\Debug\Base as Debug;
use Gideon\Handler\Group\UniformGroup;

class RoutersSpeedMeter
{
    // Utils for speed test
    private $startTime;
    private $stopTime;
    private $router;
    private $requests;


    public function __construct(Router $router, array $requests)
    {
        $this->requests = $requests;
        $this->swithRouter($router);
    }
    
    /**
     * @return mixed[] key 'request' => request obj that was dispatched
     *                 key 'route' => dispatched route obj
     */
    public function run(): array
    {
        $this->stopTime = false;
        $this->startTime = microtime(true);

        foreach($this->requests as $request)
        {
            $results[] = ['request' => $request, 'route' => $this->router->dispatch($request)];
        }

        $this->stopTime = microtime(true);
        
        return $results;
    }

    public function requestsPerSec(): float
    {
        if($this->startTime !== false && $this->stopTime !== false && !empty($this->requests))
        {
            $duration = $this->stopTime - $this->startTime;
            return (int)floor(count($this->requests) / $duration);
        }

        return -1;
    }

    public function swithRouter(Router $router)
    {
        $this->router = $router;
        $this->startTime = false;
        $this->stopTime = false;
    }
}

final class RoutersAndItsSpeedTest extends TestCase
{
    
    private $config;

    public function setUp()
    {
        $this->config = new Config('test');
    }

    public function testFastRouterEmpty(): Router
    {
        $router = new Router\FastRouter($this->config);
        $this->assertEquals(true, $router->empty());

        return $router;
    }

    public function testLoopRouterEmpty(): Router
    {
        $router = new Router\LoopRouter($this->config);
        $this->assertEquals(true, $router->empty());

        return $router;
    }


    public function generateRandomRoutes(int $routes, int $max_params, ...$routers)
    {
        $group = new UniformGroup('Gideon\Router');
        $group->addMultiple($routers);

        // Add routes
        $array[0] = ['foo', 'bar', 'param', 'var', 'test', 'value'];
        $array[1] = [':var', ':id', ':numeric', ':hash', ':word', ':/[0-9]{3}/'];
        for($i = 0; $i < $routes; ++$i)
        {
            // Generate random route
            $route = "";
            $params = mt_rand(1, $max_params);

            for($j = 0; $j < $params; ++$j)
            {
                $r = $j % 2;
                $route .= $array[$r][array_rand($array[$r], 1)] . '/';
            }
            // Generate random route - end
            $group->addRoute($route);
        }
    }

    /**
     * @depends testLoopRouterEmpty
     * @depends testFastRouterEmpty
     */
    public function testRoutersAddData($normal, $fast): array
    {
        $routes = $this->config->get('TEST_INT_ROUTES');
        $max_params = $this->config->get('TEST_INT_MAX_PARAMS');
        $this->assertGreaterThan(0, $routes);
        $this->assertGreaterThan(0, $max_params);

        $this->generateRandomRoutes($routes, $max_params, $normal, $fast);

        $this->assertNotEquals(true, $normal->empty());
        $this->assertNotEquals(true, $fast->empty());
        $this->assertEquals($routes, $normal->size());
        $this->assertEquals($routes, $fast->size());
        return [$normal, $fast];
    }

    public function testGenerateRequests(): array
    {
        $this->assertNotEquals(0, $this->config->get('TEST_INT_REQUESTS'));

        $requests = [];
        $array[0] = ['foo', 'bar', 'param', 'var', 'test', 'value'];
        $array[1] = [
                'foo', 
                (string)mt_rand(0, $this->config->get('TEST_INT_REQUESTS')), 
                (string)mt_rand(0,1).mt_rand(0, $this->config->get('TEST_INT_ROUTES')), 
                dechex(mt_rand(0, $this->config->get('TEST_INT_REQUESTS'))), 
                'bar', 
                (string)mt_rand(900, 1100)
            ];
        
        for($i = 0; $i < $this->config->get('TEST_INT_REQUESTS'); ++$i)
        {
            // Generate random request
            $request = "";
            $params = mt_rand(1, $this->config->get('TEST_INT_MAX_PARAMS'));

            for($j = 0; $j < $params; ++$j)
            {
                $r = $j % 2;
                $request .= $array[$r][array_rand($array[$r], 1)] . "/";
            }
            // Generate random request - end

            $requests[] = new Request($this->config, $request);
        }

        return $requests;
    }

    /**
     * @depends testRoutersAddData
     * @depends testGenerateRequests
     */
    public function testRoutersCompatibility($routers, $requests)
    {
        $results_normal = [];
        $results_fast = [];

        $tester = new RoutersSpeedMeter($routers[0], $requests);
        $results_normal = $tester->run();
        $rps_normal = $tester->requestsPerSec();

        $this->assertNotEquals(-1, $rps_normal);
        $this->assertEquals($this->config->get('TEST_INT_REQUESTS'), count($results_normal));
    
        $tester->swithRouter($routers[1]);
        $results_fast = $tester->run();
        $rps_fast = $tester->requestsPerSec();
        
        $this->assertNotEquals(-1, $rps_fast);
        $this->assertEquals($this->config->get('TEST_INT_REQUESTS'), count($results_fast));

        // results should be the same
        for($i = 0; $i < $this->config->get('TEST_INT_REQUESTS'); ++$i)
        {
            $this->assertEquals($results_normal[$i]['request'], $results_fast[$i]['request']);

            $this->assertEquals(
                $results_normal[$i]['route']->regex($this->config->get('FAST_ROUTER_REPLACEMENTS_DEFAULT')),
                $results_fast[$i]['route']->regex($this->config->get('FAST_ROUTER_REPLACEMENTS_DEFAULT'))
            );
            $this->assertEquals(
                $results_normal[$i]['route']->map($results_normal[$i]['request']),
                $results_fast[$i]['route']->map($results_fast[$i]['request'])
            );
            $this->assertEquals($results_normal[$i]['route']->empty(), $results_fast[$i]['route']->empty());
            $this->assertEquals($results_normal[$i]['route']->size(), $results_fast[$i]['route']->size());  
        }

        // fast router should be always faster
        $this->assertGreaterThan($rps_normal, $rps_fast);
        
        // save log
        $settings = [
            $this->config->get('TEST_INT_ROUTES'), 
            $this->config->get('TEST_INT_REQUESTS'), 
            $this->config->get('TEST_INT_MAX_PARAMS')
        ];
        $this->assertEquals(true, $this->config->log("Config: 'ROUTES' => $settings[0], 'REQUESTS' => $settings[1], 'MAX_PARAMS' => $settings[2]"));
        $this->assertEquals(true, $routers[1]->log("FastRouter Speed: $rps_fast requests per second (+ ".($rps_fast-$rps_normal).")"));
    }

}