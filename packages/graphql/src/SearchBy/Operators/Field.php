<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field as BuilderField;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithScoutSupport;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition\Condition;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

class Field extends Operator {
    use HandlerOperator;
    use WithScoutSupport;

    #[Override]
    public static function getName(): string {
        return 'field';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): string {
        return $provider->getType(Condition::class, $source, $context);
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'Field.';
    }

    #[Override]
    public function call(
        Handler $handler,
        object $builder,
        BuilderField $field,
        Argument $argument,
        Context $context,
    ): object {
        return $this->handle($handler, $builder, $field->getParent(), $argument, $context);
    }
}
