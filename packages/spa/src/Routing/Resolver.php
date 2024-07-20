<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Spa\Routing;

use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

use function array_key_exists;
use function array_map;
use function array_merge;
use function is_array;
use function is_null;
use function json_encode;
use function ksort;
use function mb_strtolower;

use const JSON_THROW_ON_ERROR;

abstract class Resolver {
    /**
     * @var array<string, mixed>
     */
    private array $resolved = [];

    public function __construct(
        private readonly Router $router,
    ) {
        // empty
    }

    // <editor-fold desc="Abstract">
    // =========================================================================
    /**
     * @param array<array-key, mixed> $parameters
     */
    abstract protected function resolve(mixed $value, array $parameters): mixed;

    /**
     * Resolves parameters for value resolving.
     *
     * @return array<array-key, mixed>
     */
    protected function resolveParameters(?Request $request = null, ?Route $route = null): array {
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
    public function get(mixed $value, ?Request $request = null, ?Route $route = null): mixed {
        $route      = $route ?: $this->router->getCurrentRoute();
        $request    = $request ?: $this->router->getCurrentRequest();
        $parameters = $this->resolveParameters($request, $route);
        $key        = $this->key(array_merge([$value], $parameters));

        if (!array_key_exists($key, $this->resolved)) {
            $this->resolved[$key] = $this->resolve($value, $parameters);
        }

        if (is_null($this->resolved[$key])) {
            throw new UnresolvedValueException($value);
        }

        return $this->resolved[$key];
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

    // <editor-fold desc="Helpers">
    // =========================================================================
    private function key(mixed $keys): string {
        if (is_array($keys)) {
            $keys = array_map(static function ($key) {
                if ($key instanceof QueueableEntity) {
                    $key = [
                        $key::class,
                        $key->getQueueableConnection(),
                        $key->getQueueableId(),
                    ];
                }

                return $key;
            }, $keys);

            ksort($keys);
        }

        return mb_strtolower(json_encode($keys, JSON_THROW_ON_ERROR));
    }
    //</editor-fold>
}
