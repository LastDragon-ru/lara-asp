<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeExec;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver as ResolverContract;
use Override;

/**
 * Path to the executable.
 *
 * @implements ResolverContract<null, string>
 */
class Resolver implements ResolverContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public function resolve(Context $context, mixed $parameters): string {
        return $context->target;
    }
}