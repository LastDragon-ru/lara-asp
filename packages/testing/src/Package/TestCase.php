<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Package;

use Illuminate\Contracts\Container\Container;
use LastDragon_ru\LaraASP\Testing\Assertions\Assertions;
use LastDragon_ru\LaraASP\Testing\Concerns\Concerns;
use LastDragon_ru\LaraASP\Testing\Utils\WithTempDirectory;
use LastDragon_ru\LaraASP\Testing\Utils\WithTempFile;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use LastDragon_ru\LaraASP\Testing\Utils\WithTranslations;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

/**
 * Special test case for packages with application.
 *
 * @required orchestra/testbench package
 */
abstract class TestCase extends TestbenchTestCase {
    use Assertions;
    use Concerns;
    use WithTestData;
    use WithTempFile;
    use WithTempDirectory;
    use WithTranslations;

    protected function getContainer(): Container {
        return $this->app;
    }
}
