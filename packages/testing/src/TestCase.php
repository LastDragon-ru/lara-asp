<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LastDragon_ru\LaraASP\Testing\Assertions\Assertions;
use LastDragon_ru\LaraASP\Testing\Concerns\Concerns;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;

use function trigger_deprecation;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '6.2.0', 'Please use own base TestCase class.');

/**
 * @deprecated 6.2.0 Please use own class.
 */
abstract class TestCase extends BaseTestCase {
    use Assertions;
    use Concerns;
    use WithTestData;
}
