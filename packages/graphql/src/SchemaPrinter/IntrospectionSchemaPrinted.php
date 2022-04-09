<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use GraphQL\Type\Definition\Type;
use GraphQL\Type\Introspection;

/**
 * @internal
 */
class IntrospectionSchemaPrinted extends SchemaPrinted {
    protected function isType(Type $type): bool {
        return Introspection::isIntrospectionType($type);
    }
}
