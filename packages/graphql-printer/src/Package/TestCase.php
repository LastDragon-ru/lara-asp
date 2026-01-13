<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Package;

use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use LastDragon_ru\PhpUnit\GraphQL\Assertions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @internal
 */
abstract class TestCase extends PHPUnitTestCase {
    use MockeryPHPUnitIntegration;
    use Assertions;
    use WithTestData;
}
