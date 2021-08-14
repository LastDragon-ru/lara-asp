<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use LastDragon_ru\LaraASP\Core\Concerns\InstanceCache;

use function array_merge;
use function is_null;

abstract class Resolver {
    use InstanceCache;

    private Router $router;

    public function __construct(Router $router) {
        $this->router = $router;
    }

    // <editor-fold desc="Abstract">
    // =========================================================================
    /**
     * @param array<mixed> $parameters
     */
    abstract protected function resolve(mixed $value, array $parameters): mixed;

    /**
     * Resolves parameters for value resolving.
     *
     * @return array<mixed>
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
     * @throws UnresolvedValueException
     */
    public function get(mixed $value, Request $request = null, Route $route = null): mixed {
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
     * @throws UnresolvedValueException
     */
    public function bind(mixed $value, Route $route): mixed {
        return $this->get($value, null, $route);
    }
    // </editor-fold>
}
