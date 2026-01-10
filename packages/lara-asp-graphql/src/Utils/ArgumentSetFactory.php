<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Utils;

use Nuwave\Lighthouse\Execution\Arguments\ArgumentSetFactory as LighthouseArgumentSetFactory;
use Nuwave\Lighthouse\Schema\Directives\RenameDirective;
use Nuwave\Lighthouse\Support\Contracts\Directive;
use Override;

/**
 * @internal
 */
class ArgumentSetFactory extends LighthouseArgumentSetFactory {
    /**
     * @inheritDoc
     */
    #[Override]
    protected function makeDefinitionMap(iterable $argumentDefinitions): array {
        $argumentDefinitionMap = [];

        foreach ($argumentDefinitions as $definition) {
            $directives = $this->directiveLocator->associated($definition);
            $rename     = $directives->first(static function (Directive $directive): bool {
                return $directive instanceof RenameDirective;
            });
            $name       = $rename instanceof RenameDirective
                ? $rename->attributeArgValue()
                : $definition->name->value;

            $argumentDefinitionMap[$name] = $definition;
        }

        return $argumentDefinitionMap;
    }
}
