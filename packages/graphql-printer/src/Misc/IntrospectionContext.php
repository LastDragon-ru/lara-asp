<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Misc;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Introspection;
use Override;

/**
 * @internal
 */
class IntrospectionContext extends Context {
    /**
     * @return array<array-key, Type&NamedType>
     */
    #[Override]
    public function getTypes(): array {
        return Introspection::getTypes();
    }

    /**
     * @return (Type&NamedType)|null
     */
    #[Override]
    public function getType(string $name): ?Type {
        return $this->getTypes()[$name] ?? null;
    }

    /**
     * @return array<array-key, Directive>
     */
    #[Override]
    public function getDirectives(): array {
        return Directive::getInternalDirectives();
    }

    #[Override]
    public function getDirective(string $name): ?Directive {
        return $this->getDirectives()[$name] ?? null;
    }
}
