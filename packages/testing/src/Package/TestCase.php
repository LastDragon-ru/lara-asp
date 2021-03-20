<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Package;

use Illuminate\Foundation\Application;
use LastDragon_ru\LaraASP\Testing\Assertions\Assertions;
use LastDragon_ru\LaraASP\Testing\Concerns\Concerns;
use LastDragon_ru\LaraASP\Testing\SetUpTraits;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

/**
 * Special test case for packages with application.
 *
 * @required orchestra/testbench package
 */
class TestCase extends TestbenchTestCase {
    use SetUpTraits;
    use Assertions;
    use Concerns;

    public function getApplication(): Application {
        return $this->app;
    }
}
