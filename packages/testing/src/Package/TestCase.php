<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Package;

use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Testing\Assertions\Assertions;
use LastDragon_ru\LaraASP\Testing\Concerns\Concerns;
use LastDragon_ru\LaraASP\Testing\Utils\WithTempDirectory;
use LastDragon_ru\LaraASP\Testing\Utils\WithTempFile;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use LastDragon_ru\LaraASP\Testing\Utils\WithTranslations;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

use function assert;

/**
 * Special test case for packages with application.
 *
 * @required orchestra/testbench package
 *
 * @internal
 */
abstract class TestCase extends TestbenchTestCase {
    use Assertions;
    use Concerns;
    use WithFaker;
    use WithConfig;
    use WithTestData;
    use WithTempFile;
    use WithTempDirectory;
    use WithTranslations;

    protected function app(): Application {
        assert($this->app !== null);

        return $this->app;
    }
}
