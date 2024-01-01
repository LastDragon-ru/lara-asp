<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LastDragon_ru\LaraASP\Testing\Assertions\Assertions;
use LastDragon_ru\LaraASP\Testing\Concerns\Concerns;
use LastDragon_ru\LaraASP\Testing\Utils\WithTestData;

abstract class TestCase extends BaseTestCase {
    use Assertions;
    use Concerns;
    use WithTestData;
}
