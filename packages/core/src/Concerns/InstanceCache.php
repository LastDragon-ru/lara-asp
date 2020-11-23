<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Closure;
use function array_key_exists;
use function json_encode;
use function mb_strtolower;

trait InstanceCache {
    private array $instanceCache = [];

    protected function instanceCacheGet($keys, Closure $closure = null) {
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

    protected function instanceCacheHas($keys): bool {
        return array_key_exists($this->instanceCacheKey($keys), $this->instanceCache);
    }

    protected function instanceCacheSet($keys, $value) {
        $this->instanceCache[$this->instanceCacheKey($keys)] = $value;

        return $value;
    }

    protected function instanceCacheUnset($keys) {
        unset($this->instanceCache[$this->instanceCacheKey($keys)]);
    }

    protected function instanceCacheClear(): void {
        $this->instanceCache = [];
    }

    private function instanceCacheKey($keys): string {
        return mb_strtolower(json_encode($keys));
    }
}
