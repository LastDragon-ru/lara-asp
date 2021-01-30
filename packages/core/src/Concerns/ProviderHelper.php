<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Concerns;

use Closure;
use Illuminate\Foundation\Application;
use ReflectionClass;
use function dirname;
use function ltrim;

/**
 * @mixin \Illuminate\Support\ServiceProvider
 */
trait ProviderHelper {
    protected function getPath(string $path): string {
        $class = new ReflectionClass(self::class);
        $path  = dirname($class->getFileName()).'/'.ltrim($path, '/');

        return $path;
    }

    protected function callAfterBoot(Closure $callback) {
        if ($this->app instanceof Application && $this->app->isBooted()) {
            $this->app->call($callback);
        } else {
            $this->booted($callback);
        }
    }
}
