<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\SchemaDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;
use Override;
use ReflectionClass;
use ReflectionClassConstant;

use function in_array;

/**
 * @internal
 */
class Schema extends SchemaDirective {
    #[Override]
    protected function getNamespace(): string {
        return Directive::Name;
    }

    #[Override]
    protected function isScalar(string $name): bool {
        if (!parent::isScalar($name)) {
            return false;
        }

        $constants = (new ReflectionClass(Operators::class))->getConstants(ReflectionClassConstant::IS_PUBLIC);
        $known     = in_array($name, $constants, true);

        return $known;
    }
}
