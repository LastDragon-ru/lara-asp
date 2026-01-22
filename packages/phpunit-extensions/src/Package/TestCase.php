<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Package;

use LastDragon_ru\PhpUnit\Assertions;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * @internal
 */
abstract class TestCase extends PHPUnitTestCase {
    use MockeryPHPUnitIntegration;
    use Assertions;
}
