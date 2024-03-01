<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators;

use GraphQL\Language\DirectiveLocation;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Types\Condition\Root;
use Override;

use function is_a;

class Child extends Operator {
    use HandlerOperator;

    /**
     * @inheritDoc
     */
    #[Override]
    protected static function getDirectiveLocations(): array {
        return [
            DirectiveLocation::SCALAR,
        ];
    }

    #[Override]
    public static function getName(): string {
        return 'child';
    }

    #[Override]
    public function isAvailable(TypeProvider $provider, TypeSource $source, Context $context): bool {
        return parent::isAvailable($provider, $source, $context)
            && $source->isObject();
    }

    #[Override]
    public function getFieldType(TypeProvider $provider, TypeSource $source, Context $context): ?string {
        return $provider->getType(Root::class, $source, $context);
    }

    #[Override]
    public function getFieldDescription(): ?string {
        return 'Field condition.';
    }

    #[Override]
    protected function isBuilderSupported(string $builder): bool {
        return is_a($builder, ScoutBuilder::class, true);
    }
}
