<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Testing;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LastDragon_ru\LaraASP\Testing\Assertions\Assertions;

abstract class TestCase extends BaseTestCase {
    use SetUpTraits, Assertions;
}
