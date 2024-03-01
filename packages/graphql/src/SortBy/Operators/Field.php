<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Operators;

use GraphQL\Language\DirectiveLocation;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Handler;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeProvider;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field as BuilderField;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\HandlerOperator;
use LastDragon_ru\LaraASP\GraphQL\Builder\Traits\WithScoutSupport;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause\Clause;
use Nuwave\Lighthouse\Execution\Arguments\Argument;
use Override;

class Field extends Operator {
    use HandlerOperator;
    use WithScoutSupport;

    /**
     * @inheritDoc
     */
    #[Override]
    protected static function getLocations(): array {
        return [
            DirectiveLocation::SCALAR,
        ];
    }

    #[Override]
    public static function getName(): string {
        return 'field';
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
