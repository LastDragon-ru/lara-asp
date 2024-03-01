<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Directives;

use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\Builder\Directives\SchemaDirective;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;
use Override;
use ReflectionClass;
use ReflectionClassConstant;

use function is_string;

/**
 * @internal
 */
class Schema extends SchemaDirective {
    #[Override]
    protected function getDirective(): string {
        return Str::camel(Directive::Name);
    }

    #[Override]
    protected function getScalar(): string {
        return Directive::Name.'Operators';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    protected function getScalars(): array {
        $constants = (new ReflectionClass(Operators::class))->getConstants(ReflectionClassConstant::IS_PUBLIC);
        $scalars   = [];

        foreach ($constants as $value) {
            if (is_string($value)) {
                $scalars[] = $value;
            }
        }

        return $scalars;
    }
}
