<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical\Logical;

/**
 * @internal Must not be used directly.
 */
class LogicalOr extends Logical {
    public function getName(): string {
        return 'or';
    }
}
