<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Contracts;

interface Settings {
    public function getSpace(): string;

    public function getIndent(): string;

    public function getFileEnd(): string;

    public function getLineEnd(): string;

    public function getLineLength(): int;

    public function isPrintDirectives(): bool;

    public function isPrintDirectiveDefinitions(): bool;

    /**
     * If `false` unused Types and Directives definition will not be printed.
     */
    public function isPrintUnusedDefinitions(): bool;

    /**
     * If `false` types and directives in the schema will be printed in the
     * original order if `true` they will be sorted by name.
     */
    public function isNormalizeDefinitions(): bool;

    /**
     * If `false` members will be printed in the original order if `true` they
     * will be sorted by name.
     */
    public function isNormalizeUnions(): bool;

    /**
     * If `false` values will be printed in the original order if `true` they
     * will be sorted by name.
     */
    public function isNormalizeEnums(): bool;

    /**
     * If `false` implemented interface list will be printed in the original
     * order if `true` they will be sorted by name.
     */
    public function isNormalizeInterfaces(): bool;

    /**
     * If `false` fields will be printed in the original order if `true` they
     * will be sorted by name.
     */
    public function isNormalizeFields(): bool;

    /**
     * If `false` arguments will be printed in the original order if `true` they
     * will be sorted by name.
     */
    public function isNormalizeArguments(): bool;

    public function isNormalizeDescription(): bool;

    /**
     * If `false` node directives will be printed in the original order if
     * `true` they will be sorted by name.
     */
    public function isNormalizeDirectives(): bool;

    /**
     * If `false` directive locations will be printed in the original order if
     * `true` they will be sorted by name.
     */
    public function isNormalizeDirectiveLocations(): bool;

    /**
     * If `true` members will always be printed multiline.
     */
    public function isAlwaysMultilineUnions(): bool;

    /**
     * If `true` arguments will always be printed multiline.
     */
    public function isAlwaysMultilineArguments(): bool;

    /**
     * If `true` implemented interfaces will always be printed multiline.
     */
    public function isAlwaysMultilineInterfaces(): bool;

    /**
     * If `true` directives will always be printed multiline.
     */
    public function isAlwaysMultilineDirectives(): bool;

    /**
     * If `true` directive locations will always be printed multiline.
     */
    public function isAlwaysMultilineDirectiveLocations(): bool;

    /**
     * Used to determine should the type definition included in output or not.
     */
    public function getTypeDefinitionFilter(): ?TypeFilter;

    /**
     * Used to determine should the type included in output or not.
     */
    public function getTypeFilter(): ?TypeFilter;

    /**
     * Used to determine should the directive included in output or not.
     */
    public function getDirectiveFilter(): ?DirectiveFilter;

    /**
     * Used to determine should the directive definition included in output or not.
     */
    public function getDirectiveDefinitionFilter(): ?DirectiveFilter;
}
