<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use GraphQL\Language\DirectiveLocation;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause\Clause;
use Override;

use function is_a;

class Child extends Operator {
    use HandlerOperator;

    /**
     * @inheritDoc
     */
    #[Override]
    protected static function locations(): array {
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
        return $provider->getType(Clause::class, $source, $context);
    }

    #[Override]
    public function getFieldDescription(): ?string {
        return 'Field clause.';
    }

    #[Override]
    protected function isBuilderSupported(string $builder): bool {
        return is_a($builder, EloquentBuilder::class, true)
            || is_a($builder, ScoutBuilder::class, true);
    }
}
