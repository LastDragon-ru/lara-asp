<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Provider;

use Illuminate\Support\ServiceProvider;

/**
 * @phpstan-require-extends ServiceProvider
 */
trait WithRoutes {
    use Helper;

    protected function bootRoutes(): void {
        $this->loadRoutesFrom($this->getPath('../defaults/routes.php'));
    }
}
