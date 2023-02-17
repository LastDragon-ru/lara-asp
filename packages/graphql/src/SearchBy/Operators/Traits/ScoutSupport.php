<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Traits;

use Laravel\Scout\Builder as ScoutBuilder;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Scout\FieldResolver;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Property;

/**
 * @mixin Property
 */
trait ScoutSupport {
    public function __construct(
        private FieldResolver $fieldResolver,
    ) {
        parent::__construct();
    }

    protected function getFieldResolver(): FieldResolver {
        return $this->fieldResolver;
    }

    public function isBuilderSupported(object $builder): bool {
        return parent::isBuilderSupported($builder)
            || $builder instanceof ScoutBuilder;
    }
}