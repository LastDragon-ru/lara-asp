<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithScoutSupport;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

/**
 * @internal Must not be used directly.
 */
class AllOf extends Logical {
    use WithScoutSupport;

    #[Override]
    public static function getName(): string {
        return 'allOf';
    }

    #[Override]
    public function getFieldDescription(): string {
        return 'All of the conditions must be true.';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): string {
        $parent = parent::getFieldType($provider, $source, $context);
        $type   = "[{$parent}!]";

        return $type;
    }

    #[Override]
    protected function getBoolean(): string {
        return 'and';
    }

    #[Override]
    public function call(
        Handler $handler,
        object $builder,
        Field $field,
        Argument $argument,
        Context $context,
    ): object {
        // Scout?
        if (!($builder instanceof ScoutBuilder)) {
            return parent::call($handler, $builder, $field, $argument, $context);
        }

        // Build
        $field      = $field->getParent();
        $conditions = $this->getConditions($argument);

        foreach ($conditions as $arguments) {
            $handler->handle($builder, $field, $arguments, $context);
        }

        // Return
        return $builder;
    }
}
