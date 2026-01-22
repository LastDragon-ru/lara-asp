<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing\Testing;

use Illuminate\Contracts\Foundation\Application;
use LastDragon_ru\LaraASP\Testing\Assertions\Assertions;
use LastDragon_ru\LaraASP\Testing\Concerns\Concerns;
use LastDragon_ru\LaraASP\Testing\Utils\WithTempDirectory;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use LastDragon_ru\LaraASP\Testing\Utils\WithTranslations;
use LastDragon_ru\PhpUnit\Assertions as PhpUnitAssertions;
use LogicException;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use Override;

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
    use WithTempDirectory;
    use WithTranslations;

    use PhpUnitAssertions {
        PhpUnitAssertions::assertDirectoryEquals insteadof Assertions;
    }

    #[Override]
    protected function app(): Application {
        return $this->app ?? throw new LogicException('Application not yet initialized.');
    }
}
