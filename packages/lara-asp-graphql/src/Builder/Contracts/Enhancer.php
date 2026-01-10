<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;

interface Enhancer {
    /**
     * @template TBuilder of object
     *
     * @param TBuilder $builder
     *
     * @return TBuilder
     */
    public function enhance(
        object $builder,
        ArgumentSet|Argument $value,
        ?Field $field = null,
        ?Context $context = null,
    ): object;
}
