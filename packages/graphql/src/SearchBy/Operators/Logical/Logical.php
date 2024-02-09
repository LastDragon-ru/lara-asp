<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Exceptions\OperatorUnsupportedBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Exceptions\OperatorInvalidArgumentValue;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Operator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition\Root;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Nuwave\Lighthouse\Execution\Arguments\ArgumentSet;
use Override;

use function array_filter;
use function count;
use function is_array;

abstract class Logical extends Operator {
    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): string {
        return $provider->getType(Root::class, $source, $context);
    }

    #[Override]
    public function call(
        Handler $handler,
        object $builder,
        Field $field,
        Argument $argument,
        Context $context,
    ): object {
        if (!($builder instanceof EloquentBuilder || $builder instanceof QueryBuilder)) {
            throw new OperatorUnsupportedBuilder($this, $builder);
        }

        $builder->where(
            function (EloquentBuilder|QueryBuilder $builder) use ($handler, $context, $field, $argument): void {
                // The last item is the name of the operator not a field
                $field      = $field->getParent();
                $conditions = $this->getConditions($argument);

                foreach ($conditions as $arguments) {
                    $builder->where(
                        static function (EloquentBuilder|QueryBuilder $builder) use (
                            $handler,
                            $context,
                            $arguments,
                            $field,
                        ): void {
                            $handler->handle($builder, $field, $arguments, $context);
                        },
                        null,
                        null,
                        $this->getBoolean(),
                    );
                }
            },
        );

        return $builder;
    }

    abstract protected function getBoolean(): string;

    /**
     * @return array<array-key, ArgumentSet>
     */
    protected function getConditions(Argument $argument): array {
        // ArgumentSet?
        $value = $argument->value;

        if ($argument->value instanceof ArgumentSet) {
            return [$argument->value];
        }

        // Array?
        $expected = 'array<'.ArgumentSet::class.'>';

        if (!is_array($value)) {
            throw new OperatorInvalidArgumentValue($this, $expected, $value);
        }

        $count = count($value);
        $args  = array_filter($value, static function (mixed $value): bool {
            return $value instanceof ArgumentSet;
        });

        if ($count !== count($args)) {
            throw new OperatorInvalidArgumentValue($this, $expected, $value);
        }

        return $args;
    }
}
