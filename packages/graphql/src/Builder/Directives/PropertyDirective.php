<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\HandlerInvalidConditions;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;

use function array_keys;
use function count;

abstract class PropertyDirective extends OperatorDirective {
    public static function getName(): string {
        return 'Property';
    }

    public function getFieldType(TypeProvider $provider, string $type): string {
        return $type;
    }

    public function isBuilderSupported(object $builder): bool {
        return true;
    }

    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
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
                array_keys($argument->value->arguments),
            );
        }

        // Apply
        return $handler->handle($builder, $property, $argument->value);
    }
}
