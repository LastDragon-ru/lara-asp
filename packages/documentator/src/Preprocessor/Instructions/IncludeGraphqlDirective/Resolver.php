<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions\IncludeGraphqlDirective;

use LastDragon_ru\LaraASP\Documentator\Preprocessor\Context;
use LastDragon_ru\LaraASP\Documentator\Preprocessor\Contracts\Resolver as ResolverContract;
use Override;

/**
 * Directive name (started with `@` sign)
 *
 * @implements ResolverContract<string, null>
 */
class Resolver implements ResolverContract {
    public function __construct() {
        // empty
    }

    #[Override]
    public function __invoke(Context $context, mixed $parameters): mixed {
        return $context->target;
    }
}
