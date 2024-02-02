<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Definitions;

use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\FieldObject;

class SortByOperatorFieldObjectDirective extends FieldObject {
    // Lighthouse loads all classes from directive namespace this leads to
    // 'Class "Orchestra\Testbench\TestCase" not found' error for our *Test
    // classes. This class required to avoid this error.
}
