<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\DirectiveFilter;

interface Settings {
    public function getSpace(): string;

    public function getIndent(): string;

    public function getFileEnd(): string;

    public function getLineEnd(): string;

    public function getLineLength(): int;

    public function isPrintUnusedTypeDefinitions(): bool;

    public function isPrintDirectives(): bool;

    /**
     * Temporary workaround to show directives when they are not supported out
     * of the box.
     *
     * @see https://github.com/graphql/graphql-playground/issues/1207
     */
    public function isPrintDirectivesInDescription(): bool;

    public function isPrintUnusedDirectiveDefinitions(): bool;

    /**
     * If `false` types and directives in the schema will be printed in the
     * original order if `true` they will be sorted by name.
     */
    public function isNormalizeSchema(): bool;

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
     * If `false` directive locations will be printed in the original order if
     * `true` they will be sorted by name.
     */
    public function isNormalizeDirectiveLocations(): bool;

    /**
     * If `true` members will always be printed multiline.
     */
    public function isAlwaysMultilineUnions(): bool;

    /**
     * Used to determine should the directive included in output or not.
     */
    public function getDirectiveFilter(): ?DirectiveFilter;
}
