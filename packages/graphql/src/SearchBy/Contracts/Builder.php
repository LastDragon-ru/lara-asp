<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;

interface Builder {
    /**
     * @template TBuilder of object
     *
     * @param TBuilder      $builder
     * @param array<string> $path
     *
     * @return TBuilder
     */
    public function where(object $builder, ArgumentSet|Argument $arguments, array $path = []): object;
}
