<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Misc;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

use function array_merge;

/**
 * @internal
 */
class Context {
    public function __construct(
        protected Settings $settings,
        protected ?DirectiveResolver $directiveResolver,
        protected ?Schema $schema,
    ) {
        // empty
    }

    public function getSettings(): Settings {
        return $this->settings;
    }

    public function getDirectiveResolver(): ?DirectiveResolver {
        return $this->directiveResolver;
    }

    public function getSchema(): ?Schema {
        return $this->schema;
    }

    /**
     * @return array<array-key, Type&NamedType>
     */
    public function getTypes(): array {
        return (array) $this->getSchema()?->getTypeMap();
    }

    /**
     * @return (Type&NamedType)|null
     */
    public function getType(string $name): ?Type {
        return $this->getSchema()?->getType($name);
    }

    /**
     * @return array<array-key, Directive>
     */
    public function getDirectives(): array {
        return array_merge(
            (array) $this->getDirectiveResolver()?->getDefinitions(),
            (array) $this->getSchema()?->getDirectives(),
        );
    }

    public function getDirective(string $name): ?Directive {
        return $this->getSchema()?->getDirective($name)
            ?? $this->getDirectiveResolver()?->getDefinition($name);
    }
}
