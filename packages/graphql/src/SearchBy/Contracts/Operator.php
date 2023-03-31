<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator as OperatorContract;

/**
 * Marks that operator is related to `@searchBy` directive.
 */
interface Operator extends OperatorContract, Scope {
    // empty
}
