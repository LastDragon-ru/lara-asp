<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;

interface Handler {
    /**
     * @template TBuilder of object
     *
     * @param TBuilder $builder
     *
     * @return TBuilder
     */
    public function handle(Context $context, object $builder, Property $property, ArgumentSet $conditions): object;
}
