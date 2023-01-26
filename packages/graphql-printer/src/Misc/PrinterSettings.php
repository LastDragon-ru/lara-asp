<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Misc;

use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Type\Definition\Directive as GraphQLDirective;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Misc\DirectiveResolver;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\TypeFilter;
use Nuwave\Lighthouse\Support\Contracts\Directive as LighthouseDirective;

/**
 * @internal
 */
class PrinterSettings implements Settings {
    public function __construct(
        protected DirectiveResolver $resolver,
        protected Settings $settings,
    ) {
        // empty
    }

    // <editor-fold desc="Getters / Settings">
    // =========================================================================
    public function getResolver(): DirectiveResolver {
        return $this->resolver;
    }
    // </editor-fold>

    // <editor-fold desc="Types">
    // =========================================================================
    public function isTypeDefinitionAllowed(Type $type): bool {
        // Allowed?
        $filter    = $this->getTypeDefinitionFilter();
        $isBuiltIn = $this->isTypeBuiltIn($type);
        $isAllowed = $isBuiltIn
            ? ($filter !== null && $filter->isAllowedType($type, $isBuiltIn))
            : ($filter === null || $filter->isAllowedType($type, $isBuiltIn));

        // Return
        return $isAllowed;
    }

    protected function isTypeBuiltIn(Type $type): bool {
        return Type::isBuiltInType($type);
    }
    // </editor-fold>

    // <editor-fold desc="Directives">
    // =========================================================================
    public function getDirective(DirectiveNode $node): GraphQLDirective|LighthouseDirective {
        return $this->resolver->getInstance($node->name->value);
    }

    public function isDirectiveAllowed(string $directive): bool {
        $filter    = $this->getDirectiveFilter();
        $resolver  = $this->getResolver();
        $isBuiltIn = $this->isDirectiveBuiltIn($directive);
        $isAllowed = $filter === null
            || $filter->isAllowedDirective($resolver->getInstance($directive), $isBuiltIn);

        return $isAllowed;
    }

    public function isDirectiveDefinitionAllowed(string $directive): bool {
        // Allowed?
        if (!$this->isDirectiveAllowed($directive)) {
            return false;
        }

        // Definition?
        $filter    = $this->getDirectiveDefinitionFilter();
        $resolver  = $this->getResolver();
        $isBuiltIn = $this->isDirectiveBuiltIn($directive);
        $isAllowed = $isBuiltIn
            ? ($filter !== null && $filter->isAllowedDirective($resolver->getInstance($directive), $isBuiltIn))
            : ($filter === null || $filter->isAllowedDirective($resolver->getInstance($directive), $isBuiltIn));

        // Return
        return $isAllowed;
    }

    protected function isDirectiveBuiltIn(string $directive): bool {
        return isset(GraphQLDirective::getInternalDirectives()[$directive]);
    }
    // </editor-fold>

    // <editor-fold desc="Settings">
    // =========================================================================
    public function getSpace(): string {
        return $this->settings->getSpace();
    }

    public function getIndent(): string {
        return $this->settings->getIndent();
    }

    public function getFileEnd(): string {
        return $this->settings->getFileEnd();
    }

    public function getLineEnd(): string {
        return $this->settings->getLineEnd();
    }

    public function getLineLength(): int {
        return $this->settings->getLineLength();
    }

    public function isPrintDirectives(): bool {
        return $this->settings->isPrintDirectives();
    }

    public function isPrintDirectiveDefinitions(): bool {
        return $this->settings->isPrintDirectiveDefinitions();
    }

    public function isPrintUnusedDefinitions(): bool {
        return $this->settings->isPrintUnusedDefinitions();
    }

    public function isNormalizeSchema(): bool {
        return $this->settings->isNormalizeSchema();
    }

    public function isNormalizeUnions(): bool {
        return $this->settings->isNormalizeUnions();
    }

    public function isNormalizeEnums(): bool {
        return $this->settings->isNormalizeEnums();
    }

    public function isNormalizeInterfaces(): bool {
        return $this->settings->isNormalizeInterfaces();
    }

    public function isNormalizeFields(): bool {
        return $this->settings->isNormalizeFields();
    }

    public function isNormalizeArguments(): bool {
        return $this->settings->isNormalizeArguments();
    }

    public function isNormalizeDescription(): bool {
        return $this->settings->isNormalizeDescription();
    }

    public function isNormalizeDirectiveLocations(): bool {
        return $this->settings->isNormalizeDirectiveLocations();
    }

    public function isAlwaysMultilineUnions(): bool {
        return $this->settings->isAlwaysMultilineUnions();
    }

    public function isAlwaysMultilineArguments(): bool {
        return $this->settings->isAlwaysMultilineArguments();
    }

    public function isAlwaysMultilineInterfaces(): bool {
        return $this->settings->isAlwaysMultilineInterfaces();
    }

    public function isAlwaysMultilineDirectiveLocations(): bool {
        return $this->settings->isAlwaysMultilineDirectiveLocations();
    }

    public function getDirectiveFilter(): ?DirectiveFilter {
        return $this->settings->getDirectiveFilter();
    }

    public function getDirectiveDefinitionFilter(): ?DirectiveFilter {
        return $this->settings->getDirectiveDefinitionFilter();
    }

    public function getTypeDefinitionFilter(): ?TypeFilter {
        return $this->settings->getTypeDefinitionFilter();
    }
    // </editor-fold>
}
