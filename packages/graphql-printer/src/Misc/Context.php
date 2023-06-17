<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Misc;

use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\NamedType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;

use function array_key_exists;
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

    // <editor-fold desc="Types">
    // =========================================================================
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

    public function isTypeAllowed(string $type): bool {
        // Filter?
        $filter = $this->getSettings()->getTypeFilter();

        if ($filter === null) {
            return true;
        }

        // Allowed?
        $isBuiltIn = $this->isTypeBuiltIn($type);
        $isAllowed = $filter->isAllowedType($type, $isBuiltIn);

        // Return
        return $isAllowed;
    }

    public function isTypeDefinitionAllowed(string $type): bool {
        // Allowed?
        if (!$this->isTypeAllowed($type)) {
            return false;
        }

        // Allowed?
        $filter    = $this->getSettings()->getTypeDefinitionFilter();
        $isBuiltIn = $this->isTypeBuiltIn($type);
        $isAllowed = $isBuiltIn
            ? ($filter !== null && $filter->isAllowedType($type, $isBuiltIn))
            : ($filter === null || $filter->isAllowedType($type, $isBuiltIn));

        // Return
        return $isAllowed;
    }

    protected function isTypeBuiltIn(string $type): bool {
        return array_key_exists($type, Type::builtInTypes());
    }
    // </editor-fold>

    // <editor-fold desc="Directives">
    // =========================================================================
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

    public function isDirectiveAllowed(string $directive): bool {
        // Filter?
        $filter = $this->getSettings()->getDirectiveFilter();

        if ($filter === null) {
            return true;
        }

        // Allowed?
        $isBuiltIn = $this->isDirectiveBuiltIn($directive);
        $isAllowed = $filter->isAllowedDirective($directive, $isBuiltIn);

        // Return
        return $isAllowed;
    }

    public function isDirectiveDefinitionAllowed(string $directive): bool {
        // Allowed?
        if (!$this->getSettings()->isPrintDirectiveDefinitions() || !$this->isDirectiveAllowed($directive)) {
            return false;
        }

        // Definition?
        $filter    = $this->getSettings()->getDirectiveDefinitionFilter();
        $isBuiltIn = $this->isDirectiveBuiltIn($directive);
        $isAllowed = $isBuiltIn
            ? ($filter !== null && $filter->isAllowedDirective($directive, $isBuiltIn))
            : ($filter === null || $filter->isAllowedDirective($directive, $isBuiltIn));

        // Return
        return $isAllowed;
    }

    protected function isDirectiveBuiltIn(string $directive): bool {
        return isset(GraphQLDirective::getInternalDirectives()[$directive]);
    }
    // </editor-fold>
}
