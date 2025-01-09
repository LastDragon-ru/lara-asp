<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Defaults;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\BuilderFieldResolver as BuilderFieldResolverContract;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver as ScoutFieldResolver;
use LastDragon_ru\LaraASP\GraphQL\Builder\Field;
use LastDragon_ru\LaraASP\GraphQL\Builder\Property;
use Override;

use function implode;

/**
 * @internal
 */
final readonly class BuilderFieldResolver implements BuilderFieldResolverContract {
    public function __construct(
        private ?ScoutFieldResolver $resolver = null,
    ) {
        // empty
    }

    #[Override]
    public function getField(object $builder, Field $field): string {
        return $builder instanceof ScoutBuilder && $this->resolver !== null
            ? $this->resolver->getField($builder->model, new Property(...$field->getPath()))
            : implode('.', $field->getPath());
    }
}
