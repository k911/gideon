<?php
namespace Gideon\Router;

use Gideon\Http\Request;
use Gideon\Handler\Config;

/**
 * Config keys used:
 * - FAST_ROUTER_MAX_CHUNKS
 */

class FastRouter extends Base
{

    /**
     * @var FastRouter\RegexRoute[] $maps key => HTTP_METHOD, values => map of FastRouter\RegexRoute for dispatcher
     * @var string[]                $chunks key => HTTP_METHOD, values => chunks containing string regexes for fast dispatcher
     * @var int                     $max number of chunks in HTTP_METHOD that router can create
     */
    private $maps;
    private $chunks;
    private $max;

    private function computeChunkSize(string $method)
    {
        $elements = count($this->routes[$method]);
        $chunks = round($elements / $this->max);
        return ($chunks) ? ceil($elements/ $chunks) : $elements;
    } 

    protected function prepare(string $method)
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

    public function dispatch(Request $request): Route
    {
        $method = $request->method();
        $uri = $request->uri();

        if(empty($this->chunks[$method]))
            $this->prepare($method);

        $maps = $this->maps[$method];
        $chunks = $this->chunks[$method];
        foreach ($chunks as $i => $regex) 
        {
            if (preg_match($regex, $uri, $matches) === 1)
            {
                return $maps[$i][count($matches)];
            }
        }

        return new Route\EmptyRoute();
    }

    protected function routeFrom(string $route, callable $callback = null): Route
    {
        return new Route\RegexRoute($route, $callback);
    }

    public function __construct(Config $config)
    {
        parent::__construct($config);
        $this->max = $config->get('FAST_ROUTER_MAX_CHUNKS');

        foreach($this->supportedMethods as $method)
        {
            $this->chunks[$method] = [];
            $this->maps[$method] = [];
        }
    }

    /**
     * @override
     */
    protected function getDebugProperties(): array
    {
        $debugarr = parent::getDebugProperties();
        $debugarr['maps'] = $this->maps;
        $debugarr['chunks'] = $this->chunks;
        return $debugarr;
    }
}