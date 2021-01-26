<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Core\Concerns\InstanceCache;
use function array_merge;

abstract class Resolver {
    use InstanceCache;

    private Router $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    // <editor-fold desc="Abstract">
    // =========================================================================
    /**
     * Resolves value.
     *
     * @param mixed $value
     * @param array $parameters
     *
     * @return mixed|null
     */
    protected abstract function resolve($value, array $parameters);

    /**
     * Resolves parameters for value resolving.
     *
     * @param \Illuminate\Http\Request|null  $request
     * @param \Illuminate\Routing\Route|null $route
     *
     * @return array
     */
    protected function resolveParameters(Request $request = null, Route $route = null): array {
        return [];
    }
    // </editor-fold>

    // <editor-fold desc="API">
    // =========================================================================
    /**
     * Returns value.
     *
     * @param mixed                          $value
     * @param \Illuminate\Http\Request|null  $request
     * @param \Illuminate\Routing\Route|null $route
     *
     * @throws \LastDragon_ru\LaraASP\Core\Routing\UnresolvedValueException
     *
     * @return mixed
     */
    public function get($value, Request $request = null, Route $route = null) {
        $route      = $route ?: $this->router->getCurrentRoute();
        $request    = $request ?: $this->router->getCurrentRequest();
        $parameters = $this->resolveParameters($request, $route);
        $key        = array_merge([$value], $parameters);
        $resolved   = $this->instanceCacheGet($key, function () use ($value, $parameters) {
            return $this->resolve($value, $parameters);
        });

        if (is_null($resolved)) {
            throw new UnresolvedValueException($value);
        }

        return $resolved;
    }

    /**
     * Returns the value (used while substituting bindings)
     *
     * @param mixed                     $value
     * @param \Illuminate\Routing\Route $route
     *
     * @throws \LastDragon_ru\LaraASP\Core\Routing\UnresolvedValueException
     *
     * @return mixed
     */
    public function bind($value, Route $route) {
        return $this->get($value, null, $route);
    }
    // </editor-fold>
}
