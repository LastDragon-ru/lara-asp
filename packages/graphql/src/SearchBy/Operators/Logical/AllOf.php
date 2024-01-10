<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Traits\ScoutSupport;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

/**
 * @internal Must not be used directly.
 */
class AllOf extends Logical {
    use ScoutSupport;

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
        return "[{$provider->getType(Condition::class, $source, $context)}!]";
    }

    #[Override]
    protected function getBoolean(): string {
        return 'and';
    }

    #[Override]
    public function call(
        Handler $handler,
        Context $context,
        object $builder,
        Property $property,
        Argument $argument,
    ): object {
        // Scout?
        if (!($builder instanceof ScoutBuilder)) {
            return parent::call($handler, $context, $builder, $property, $argument);
        }

        // Build
        $property   = $property->getParent();
        $conditions = $this->getConditions($argument);

        foreach ($conditions as $arguments) {
            $handler->handle($context, $builder, $property, $arguments);
        }

        // Return
        return $builder;
    }
}
