<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\HandlerInvalidConditions;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Override;

use function count;

abstract class PropertyDirective extends OperatorDirective {
    #[Override]
    public static function getName(): string {
        return 'property';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source): string {
        return $source->getTypeName();
    }

    #[Override]
    public function isBuilderSupported(string $builder): bool {
        return true;
    }

    #[Override]
    public function call(
        Handler $handler,
        Context $context,
        object $builder,
        Property $property,
        Argument $argument,
    ): object {
        if (!($argument->value instanceof ArgumentSet)) {
            throw new HandlerInvalidConditions($handler);
        }

        // Empty?
        if (count($argument->value->arguments) === 0) {
            throw new ConditionEmpty();
        }

        // Valid?
        if (count($argument->value->arguments) > 1) {
            throw new ConditionTooManyOperators(
                ArgumentFactory::getArgumentsNames($argument->value),
            );
        }

        // Apply
        return $handler->handle($context, $builder, $property, $argument->value);
    }
}
