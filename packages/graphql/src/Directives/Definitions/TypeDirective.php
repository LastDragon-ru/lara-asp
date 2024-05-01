<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Directives\Definitions;

use LastDragon_ru\LaraASP\GraphQL\Directives\Type;

class TypeDirective extends Type {
    // Lighthouse loads all classes from directive namespace this leads to
    // 'Class "Orchestra\Testbench\TestCase" not found' error for our *Test
    // classes. This class required to avoid this error.
}
