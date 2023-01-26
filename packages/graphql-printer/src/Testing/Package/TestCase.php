<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package;

use LastDragon_ru\LaraASP\Testing\Concerns\StrictAssertEquals;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase {
    use StrictAssertEquals;
    use WithTestData;
}
