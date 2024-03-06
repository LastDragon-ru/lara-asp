<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter;

use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveFilter;
use Nuwave\Lighthouse\Exceptions\DirectiveException;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Override;
use ReflectionClass;

use function explode;
use function str_starts_with;

/**
 * @internal
 */
class LighthouseDirectiveFilter implements DirectiveFilter {
    public function __construct(
        protected DirectiveLocator $locator,
    ) {
        // empty
    }

    #[Override]
    public function isAllowedDirective(string $directive, bool $isStandard): bool {
        // Standard?
        if ($isStandard) {
            return false;
        }

        // Lighthouse?
        $isLighthouse = false;

        try {
            $class        = new ReflectionClass($this->locator->resolve($directive));
            $isAnonymous  = $class->isAnonymous();
            $isLighthouse = !$isAnonymous
                && str_starts_with($class->getName(), explode('\\', BaseDirective::class)[0]);
        } catch (DirectiveException) {
            // empty
        }

        return !$isLighthouse;
    }
}
