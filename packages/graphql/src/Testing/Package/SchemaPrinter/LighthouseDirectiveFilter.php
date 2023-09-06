<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter;

use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveFilter;
use Nuwave\Lighthouse\Exceptions\DirectiveException;
use Nuwave\Lighthouse\Schema\DirectiveLocator;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;

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

    public function isAllowedDirective(string $directive, bool $isStandard): bool {
        // Standard?
        if ($isStandard) {
            return false;
        }

        // Lighthouse?
        $isLighthouse = false;

        try {
            $class        = $this->locator->resolve($directive);
            $isLighthouse = str_starts_with($class, explode('\\', BaseDirective::class)[0]);
        } catch (DirectiveException) {
            // empty
        }

        return !$isLighthouse;
    }
}
