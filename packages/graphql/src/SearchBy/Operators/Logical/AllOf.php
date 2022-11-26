<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Logical;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\WithScoutSupport;
use Nuwave\Lighthouse\Execution\Arguments\Argument;

/**
 * @internal Must not be used directly.
 */
class AllOf extends Logical {
    use WithScoutSupport;

    public static function getName(): string {
        return 'allOf';
    }

    public function getFieldDescription(): string {
        return 'All of the conditions must be true.';
    }

    public function getFieldType(TypeProvider $provider, string $type, ?bool $nullable): string {
        return "[{$type}!]";
    }

    protected function getBoolean(): string {
        return 'and';
    }

    public function call(Handler $handler, object $builder, Property $property, Argument $argument): object {
        // Scout?
        if (!($builder instanceof ScoutBuilder)) {
            return parent::call($handler, $builder, $property, $argument);
        }

        // Build
        $property   = $property->getParent();
        $conditions = $this->getConditions($argument);

        foreach ($conditions as $arguments) {
            $handler->handle($builder, $property, $arguments);
        }

        // Return
        return $builder;
    }
}
