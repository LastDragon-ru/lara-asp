<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Traits;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\HandlerInvalidConditions;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Override;

use function count;

/**
 * @phpstan-require-implements Operator
 */
trait HandlerOperator {
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

    /**
     * @template TBuilder of object
     *
     * @param TBuilder $builder
     *
     * @return TBuilder
     */
    private function handle(
        Handler $handler,
        object $builder,
        Property $property,
        Argument $argument,
        Context $context,
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
        return $handler->handle($builder, $property, $argument->value, $context);
    }
}
