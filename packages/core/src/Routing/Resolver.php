<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Routing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
     * Resolves model.
     *
     * @param mixed $value
     * @param array $parameters
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    protected abstract function resolve($value, array $parameters): ?Model;

    /**
     * Resolves parameters for model resolving.
     *
     * @param \Illuminate\Http\Request|null  $request
     * @param \Illuminate\Routing\Route|null $route
     *
     * @return array
     */
    protected abstract function resolveParameters(Request $request = null, Route $route = null): array;
    // </editor-fold>

    // <editor-fold desc="API">
    // =========================================================================
    /**
     * Returns model.
     *
     * @param mixed                          $value
     * @param \Illuminate\Http\Request|null  $request
     * @param \Illuminate\Routing\Route|null $route
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function get($value, Request $request = null, Route $route = null): Model {
        $route      = $route ?: $this->router->getCurrentRoute();
        $request    = $request ?: $this->router->getCurrentRequest();
        $parameters = $this->resolveParameters($request, $route);
        $key        = array_merge([$value], $parameters);
        $resolved   = $this->instanceCacheGet($key, function () use ($value, $parameters): ?Model {
            return $this->resolve($value, $parameters);
        });

        if (is_null($resolved)) {
            throw (new ModelNotFoundException())->setModel(static::class);
        }

        return $resolved;
    }

    /**
     * Returns the model (used while substituting bindings)
     *
     * @param mixed                     $value
     * @param \Illuminate\Routing\Route $route
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function bind($value, Route $route): Model {
        return $this->get($value, null, $route);
    }
    // </editor-fold>
}
