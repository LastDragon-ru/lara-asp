<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;

interface Handler {
    /**
     * @template TBuilder of object
     *
     * @param TBuilder $builder
     *
     * @return TBuilder
     */
    public function handle(object $builder, Field $field, ArgumentSet $conditions, Context $context): object;
}
