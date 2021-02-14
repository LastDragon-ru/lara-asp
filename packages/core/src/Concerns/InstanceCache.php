<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Closure;
use Illuminate\Contracts\Queue\QueueableEntity;

use function array_key_exists;
use function array_map;
use function get_class;
use function is_array;
use function json_encode;
use function ksort;
use function mb_strtolower;

trait InstanceCache {
    private array $instanceCache = [];

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
            $keys = array_map(function ($key) {
                if ($key instanceof QueueableEntity) {
                    $key = [
                        get_class($key),
                        $key->getQueueableConnection(),
                        $key->getQueueableId(),
                    ];
                }

                return $key;
            }, $keys);

            ksort($keys);
        }

        return mb_strtolower(json_encode($keys));
    }
}
