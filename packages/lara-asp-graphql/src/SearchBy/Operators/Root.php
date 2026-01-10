<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithScoutSupport;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition\Root as RootType;
use Override;

class Root extends Operator {
    use HandlerOperator;
    use WithScoutSupport;

    #[Override]
    public static function getName(): string {
        return 'root';
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): ?string {
        return $provider->getType(RootType::class, $source, $context);
    }

    #[Override]
    public function getFieldDescription(): ?string {
        return 'Directive root.';
    }
}
