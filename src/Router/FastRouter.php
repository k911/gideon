<?php
namespace Gideon\Router;

use Gideon\Http\Request;
use Gideon\Config;

/**
 * Config keys used:
 * - FAST_ROUTER_MAX_CHUNKS
 */

class FastRouter extends Base
{

    /**
     * Map for chunks to provide route object from unique index number
     * Array<string(name of the http method), FastRouter\RegexRoute[]>
     * Chunks and maps for one method are bound by array indexes
     * @var array
     */
    private $maps;

    /**
     * Max number of chunks for one supported http method
     * @var int
     */
    private $max;

    /**
     * Array<string(name of the http method), array(of regex string chunks covering some routes)>
     * Chunks and maps for one method are bound by array indexes
     * @var array
     */
    private $chunks;

    /**
     * Computes how many routes one chunk should cover
     * @param string $method name of http method
     * @return int
     */
    private function computeChunkSize(string $method): int
    {
        $elements = count($this->routes[$method]);
        $chunks = round($elements / $this->max);
        return ($chunks) ? ceil($elements/ $chunks) : $elements;
    }

    protected function prepare(string $method)
    {
        $chunk_size = $this->computeChunkSize($method);
        $iterator = $this->routes[$method]->getIterator();

        while ($iterator->valid()) {
            $regexes = [];
            $map = [];
            $dummies = 0;
            for ($i = 0; $i < $chunk_size; ++$i) {
                $route = $iterator->current();
                $vars = $route->variables();
                $dummies = max($dummies, $vars);
                $regexes[] = $route->toPattern($this->replacements) . str_repeat('()', $dummies - $vars);
                ++$dummies;
                $map[$dummies] = $route;
                $iterator->next();
            }

            $this->chunks[$method][] = '~^(?|' . implode('|', $regexes) . ')$~';
            $this->maps[$method][] = $map;
        }
    }

    public function dispatch(Request $request): Route
    {
        $method = $request->getMethod();
        $uri = $request->getURI();

        if (empty($this->chunks[$method])) {
            $this->prepare($method);
        }

        $maps = $this->maps[$method];
        $chunks = $this->chunks[$method];
        foreach ($chunks as $i => $regex) {
            if (preg_match($regex, $uri, $matches) === 1) {
                return $maps[$i][count($matches)];
            }
        }

        return $this->defaultRoute;
    }

    public function createRouteFrom(string $route, callable $callback = null): Route
    {
        return new Route\RegexRoute($route, $callback);
    }

    public function __construct(Config $config)
    {
        parent::__construct($config);
        $this->max = $config->get('FAST_ROUTER_MAX_CHUNKS');

        foreach ($this->supportedMethods as $method) {
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
