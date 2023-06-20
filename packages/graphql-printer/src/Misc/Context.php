<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Misc;

use GraphQL\Language\AST\DirectiveDefinitionNode;
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
    /**
     * @var array<string, bool>
     */
    private array $allowedTypes = [];

    /**
     * @var array<string, bool>
     */
    private array $allowedTypesDefinitions = [];

    /**
     * @var array<string, bool>
     */
    private array $allowedDirectives = [];

    /**
     * @var array<string, bool>
     */
    private array $allowedDirectivesDefinitions = [];

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
        // Cached?
        if (isset($this->allowedTypes[$type])) {
            return $this->allowedTypes[$type];
        }

        // Allowed?
        $isAllowed = true;
        $filter    = $this->getSettings()->getTypeFilter();

        if ($filter !== null) {
            $isBuiltIn = $this->isTypeBuiltIn($type);
            $isAllowed = $filter->isAllowedType($type, $isBuiltIn);
        }

        // Cache
        $this->allowedTypes[$type] = $isAllowed;

        // Return
        return $isAllowed;
    }

    public function isTypeDefinitionAllowed(string $type): bool {
        // Cached?
        if (isset($this->allowedTypesDefinitions[$type])) {
            return $this->allowedTypesDefinitions[$type];
        }

        // Allowed?
        $isAllowed = $this->isTypeAllowed($type);

        if ($isAllowed) {
            $filter    = $this->getSettings()->getTypeDefinitionFilter();
            $isBuiltIn = $this->isTypeBuiltIn($type);
            $isAllowed = $isBuiltIn
                ? ($filter !== null && $filter->isAllowedType($type, $isBuiltIn))
                : ($filter === null || $filter->isAllowedType($type, $isBuiltIn));
        }

        // Cache
        $this->allowedTypesDefinitions[$type] = $isAllowed;

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
     * @return array<array-key, DirectiveDefinitionNode|Directive>
     */
    public function getDirectives(): array {
        return array_merge(
            (array) $this->getDirectiveResolver()?->getDefinitions(),
            (array) $this->getSchema()?->getDirectives(),
        );
    }

    public function getDirective(string $name): DirectiveDefinitionNode|Directive|null {
        return $this->getSchema()?->getDirective($name)
            ?? $this->getDirectiveResolver()?->getDefinition($name);
    }

    public function isDirectiveAllowed(string $directive): bool {
        // Cached?
        if (isset($this->allowedDirectives[$directive])) {
            return $this->allowedDirectives[$directive];
        }

        // Allowed?
        $isAllowed = true;
        $filter    = $this->getSettings()->getDirectiveFilter();

        if ($filter !== null) {
            $isBuiltIn = $this->isDirectiveBuiltIn($directive);
            $isAllowed = $filter->isAllowedDirective($directive, $isBuiltIn);
        }

        // Cache
        $this->allowedDirectives[$directive] = $isAllowed;

        // Return
        return $isAllowed;
    }

    public function isDirectiveDefinitionAllowed(string $directive): bool {
        // Cached?
        if (isset($this->allowedDirectivesDefinitions[$directive])) {
            return $this->allowedDirectivesDefinitions[$directive];
        }

        // Allowed?
        $settings  = $this->getSettings();
        $isAllowed = $settings->isPrintDirectiveDefinitions()
            && $this->isDirectiveAllowed($directive);

        if ($isAllowed) {
            $filter    = $settings->getDirectiveDefinitionFilter();
            $isBuiltIn = $this->isDirectiveBuiltIn($directive);
            $isAllowed = $isBuiltIn
                ? ($filter !== null && $filter->isAllowedDirective($directive, $isBuiltIn))
                : ($filter === null || $filter->isAllowedDirective($directive, $isBuiltIn));
        }

        // Cache
        $this->allowedDirectivesDefinitions[$directive] = $isAllowed;

        // Return
        return $isAllowed;
    }

    protected function isDirectiveBuiltIn(string $directive): bool {
        return isset(GraphQLDirective::getInternalDirectives()[$directive]);
    }
    // </editor-fold>
}
