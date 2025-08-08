<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Package;

use LastDragon_ru\GraphQLPrinter\Testing\GraphQLAssertions;
use LastDragon_ru\LaraASP\Testing\Concerns\StrictAssertEquals;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @internal
 */
abstract class TestCase extends PHPUnitTestCase {
    use MockeryPHPUnitIntegration;
    use StrictAssertEquals;
    use GraphQLAssertions;
    use WithTestData;
}
