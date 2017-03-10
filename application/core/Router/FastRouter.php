<?php
namespace Gideon\Router;

use Gideon\Router;
use Gideon\Http\Request;
use Gideon\Debug\Base as Debug;
use Gideon\Handler\Config;

class FastRouter extends Debug implements Router
{

    /**
     * @var array                   $routes key => HTTP_METHOD, values => ArrayObject of FastRouter\RegexRoute
     * @var FastRouter\RegexRoute[] $maps key => HTTP_METHOD, values => map of FastRouter\RegexRoute for dispatcher
     * @var string[]                $chunks key => HTTP_METHOD, values => chunks containing string regexes for fast dispatcher
     * @var int                     $max number of chunks in HTTP_METHOD that router can create
     * @var string[]                $replacements key => name to replace in RegexRoute\Param()->value with regex
     */
    private $routes;
    private $maps;
    private $chunks;
    private $max;
    private $replacements;

    private function computeChunkSize(string $method)
    {
        $elements = count($this->routes[$method]);
        $chunks = round($elements / $this->max);
        return ($chunks) ? ceil($elements/ $chunks) : $elements;
    } 

    public function prepareAll()
    {
        $methods = array_keys($this->routes);
        foreach($methods as $method)
        {
            $this->prepare($method);
        }
    }

    public function prepare(string $method)
    {
        $chunk_size = $this->computeChunkSize($method);
        $iterator = $this->routes[$method]->getIterator();

        while($iterator->valid())
        {
            $regexes = [];
            $map = [];
            $dummies = 0;
            for($i = 0; $i < $chunk_size; ++$i)
            {
                $route = $iterator->current();

                $vars = $route->variables();
                $dummies = max($dummies, $vars);
                $regexes[] = $route->regex($this->replacements) . str_repeat('()', $dummies - $vars);
                ++$dummies;

                // dunno if needed
                $map[$dummies] = $route;
                
                $iterator->next();
            }
            
            $this->chunks[$method][] = '~^(?|' . implode('|', $regexes) . ')$~';
            $this->maps[$method][] = $map;
        }
    }

    /**
     * @api
     * @param Request
     * @return Route | when not found route is empty()
     */
    public function dispatch(Request $request): Router\Route
    {
        $method = $request->method();

        // Secure from situation when trying to dispatch having no routes for method
        if(isset($this->routes[$method]) && $this->routes[$method]->count())
        {
            if(empty($this->chunks[$method]))
                $this->prepare($method);

            $uri = $request->uri();
            $method_maps = $this->maps[$method];
            $method_chunks = $this->chunks[$method];

            foreach ($method_chunks as $i => $regex) 
            {
                if (preg_match($regex, $uri, $matches) === 1)
                {
                    return $method_maps[$i][count($matches)] ?? new Route\UndefinedRoute(); // Todo throw error
                }
            }
        }

        return new Route\EmptyRoute();
    }

    /**
     * @api
     */
    public function addRoute(string $route, callable $handler = null, string $method = 'GET'): Router\Route
    {
        $method = strtoupper($method);
        $route = new Route\RegexRoute($route, $handler);
        if(!$route->empty())
        {
            if(!isset($this->routes[$method]))
            {
                $this->routes[$method] = new \ArrayObject();
            }
            $this->routes[$method][] = $route;
        }

        return $route;
    }

    public function __construct(Config $config)
    {
        $this->max = $config->get('FAST_ROUTER_MAX_CHUNKS');
        $this->replacements = $config->get('FAST_ROUTER_REPLACEMENTS_DEFAULT');
    }

    public function size(): int 
    {
        $count = 0;
        foreach($this->routes as $method_routes)
        {
            $count += $method_routes->count();
        }
        return $count;
    }

    public function empty(): bool
    {
        return empty($this->routes);
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        $debugarr = ['maps' => $this->maps,
                'chunks' => $this->chunks];

        $methods = array_keys($this->routes);
        foreach($methods as $method)
        {
            $debugarr[$method] = $this->routes[$method];
        }
        return $debugarr;
    }
}