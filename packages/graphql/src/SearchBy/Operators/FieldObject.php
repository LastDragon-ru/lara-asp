<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition;
use Override;

use function is_a;

class FieldObject extends BaseOperator {
    use HandlerOperator;

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
        return 'Field condition.';
    }

    #[Override]
    public function isAvailable(string $builder, Context $context): bool {
        return is_a($builder, ScoutBuilder::class, true);
    }
}
