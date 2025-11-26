<?php declare(strict_types = 1);

namespace LastDragon_ru\Path\Package;

use LastDragon_ru\LaraASP\Testing\Concerns\StrictAssertEquals;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @internal
 */
abstract class TestCase extends PHPUnitTestCase {
    use MockeryPHPUnitIntegration;
    use StrictAssertEquals;
}
