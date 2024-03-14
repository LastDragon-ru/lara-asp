<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithScoutSupport;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Enumeration;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Scalar;
use Override;

class Condition extends Operator {
    use HandlerOperator;
    use WithScoutSupport;

    /**
     * @inheritDoc
     */
    #[Override]
    protected static function locations(): array {
        return [
            // empty
        ];
    }

    #[Override]
    public static function getName(): string {
        return 'condition';
    }

    #[Override]
    public function isAvailable(TypeProvider $provider, TypeSource $source, Context $context): bool {
        return parent::isAvailable($provider, $source, $context)
            && ($source->isScalar() || $source->isEnum());
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): ?string {
        return match (true) {
            $source->isScalar() => $provider->getType(Scalar::class, $source, $context),
            $source->isEnum()   => $provider->getType(Enumeration::class, $source, $context),
            default             => null,
        };
    }

    #[Override]
    public function getFieldDescription(): ?string {
        return 'Field condition.';
    }
}
