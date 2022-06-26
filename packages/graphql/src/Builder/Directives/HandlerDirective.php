<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Directives;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Operator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionEmpty;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyOperators;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\Client\ConditionTooManyProperties;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\HandlerInvalidConditions;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\Utils\ArgumentFactory;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Utils;

use function array_keys;
use function count;

abstract class HandlerDirective extends BaseDirective implements Handler {
    public function __construct(
        private Container $container,
        private ArgumentFactory $factory,
    ) {
        // empty
    }

    protected function getContainer(): Container {
        return $this->container;
    }

    protected function getFactory(): ArgumentFactory {
        return $this->factory;
    }

    /**
     * @template T of object
     *
     * @param T $builder
     *
     * @return T
     */
    protected function handleAnyBuilder(object $builder, mixed $value): object {
        if ($value !== null) {
            $argument = $this->getFactory()->getArgument($this->definitionNode, $value);

            if ($argument->value instanceof ArgumentSet) {
                $builder = $this->handle($builder, new Property(), $argument->value);
            } else {
                throw new HandlerInvalidConditions($this);
            }
        }

        return $builder;
    }

    /**
     * @template T of object
     *
     * @param T $builder
     *
     * @return T
     */
    public function handle(object $builder, Property $property, ArgumentSet $conditions): object {
        // Empty?
        if (count($conditions->arguments) === 0) {
            return $builder;
        }

        // Valid?
        if (count($conditions->arguments) !== 1) {
            throw new ConditionTooManyProperties(array_keys($conditions->arguments));
        }

        // Call
        return $this->call($builder, $property, $conditions);
    }

    /**
     * @template T of object
     *
     * @param T $builder
     *
     * @return T
     */
    protected function call(object $builder, Property $property, ArgumentSet $operator): object {
        // Operator & Value
        /** @var Operator|null $op */
        $op     = null;
        $value  = null;
        $filter = Utils::instanceofMatcher(Operator::class);

        if (count($operator->arguments) > 1) {
            throw new ConditionTooManyOperators(
                array_keys($operator->arguments),
            );
        }

        foreach ($operator->arguments as $name => $argument) {
            /** @var Collection<int, Operator> $operators */
            $operators = $argument->directives->filter($filter);
            $property  = $property->getChild($name);
            $value     = $argument;
            $op        = $operators->first();

            if (count($operators) > 1) {
                throw new ConditionTooManyOperators(
                    $operators
                        ->map(static function (Operator $operator): string {
                            return $operator::getName();
                        })
                        ->all(),
                );
            }
        }

        // Operator?
        if (!$op || !$value) {
            throw new ConditionEmpty();
        }

        // Supported?
        if (!$op->isBuilderSupported($builder)) {
            throw new OperatorUnsupportedBuilder($op, $builder);
        }

        // Return
        return $op->call($this, $builder, $property, $value);
    }
}
