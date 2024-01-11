<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\PropertyOperator;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

abstract class PropertyDirective extends OperatorDirective {
    use PropertyOperator;

    #[Override]
    public static function getName(): string {
        return 'property';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): string {
        return $source->getTypeName();
    }

    #[Override]
    public function isBuilderSupported(string $builder): bool {
        return true;
    }

    #[Override]
    public function call(
        Handler $handler,
        object $builder,
        Property $property,
        Argument $argument,
        Context $context,
    ): object {
        return $this->handle($handler, $builder, $property, $argument, $context);
    }
}
