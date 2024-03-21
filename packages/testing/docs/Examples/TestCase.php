<?php declare(strict_types = 1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LastDragon_ru\LaraASP\Testing\Assertions\Assertions;
use LastDragon_ru\LaraASP\Testing\Concerns\Concerns;

abstract class TestCase extends BaseTestCase {
    use Assertions;         // Added
    use Concerns;           // Added
    use CreatesApplication;
}
