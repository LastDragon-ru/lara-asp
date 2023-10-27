<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Closure;
use Illuminate\Contracts\Queue\QueueableEntity;

use function array_key_exists;
use function array_map;
use function is_array;
use function json_encode;
use function ksort;
use function mb_strtolower;

use const JSON_THROW_ON_ERROR;

/**
 * @deprecated 5.0.0
 */
trait InstanceCache {
    /**
     * @var array<string, mixed>
     */
    private array $instanceCache = [];

    /**
     * @param Closure():mixed|null $closure
     */
    protected function instanceCacheGet(mixed $keys, Closure $closure = null): mixed {
        $key   = $this->instanceCacheKey($keys);
        $value = null;

        if (array_key_exists($key, $this->instanceCache)) {
            $value = $this->instanceCache[$key];
        } elseif ($closure) {
            $value = $this->instanceCache[$key] = $closure();
        } else {
            // no action
        }

        return $value;
    }

    protected function instanceCacheHas(mixed $keys): bool {
        return array_key_exists($this->instanceCacheKey($keys), $this->instanceCache);
    }

    protected function instanceCacheSet(mixed $keys, mixed $value): mixed {
        $this->instanceCache[$this->instanceCacheKey($keys)] = $value;

        return $value;
    }

    protected function instanceCacheUnset(mixed $keys): mixed {
        $key   = $this->instanceCacheKey($keys);
        $value = $this->instanceCache[$key] ?? null;

        unset($this->instanceCache[$key]);

        return $value;
    }

    protected function instanceCacheClear(): void {
        $this->instanceCache = [];
    }

    protected function instanceCacheKey(mixed $keys): string {
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
}
