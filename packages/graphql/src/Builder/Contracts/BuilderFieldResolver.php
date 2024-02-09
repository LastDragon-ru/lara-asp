<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Builder\Field;

/**
 * Convert {@see Field} into builder field/column/etc.
 */
interface BuilderFieldResolver {
    public function getField(object $builder, Field $field): string;
}
