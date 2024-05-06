<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExec;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\TargetResolver;
use Override;

/**
 * Path to the executable.
 *
 * @template TParameters
 *
 * @implements TargetResolver<TParameters, string>
 */
class Target implements TargetResolver {
    public function __construct() {
        // empty
    }

    #[Override]
    public function resolve(Context $context, mixed $parameters): string {
        return $context->target;
    }
}
