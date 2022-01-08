<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\DirectiveFilter;

// TODO: Directive resolver
// TODO: Throw error if directive definition not found

interface Settings {
    public function getSpace(): string;

    public function getIndent(): string;

    public function getFileEnd(): string;

    public function getLineEnd(): string;

    public function getLineLength(): int;

    public function isIncludeUnusedTypeDefinitions(): bool;

    public function isIncludeDirectives(): bool;

    public function isIncludeDirectivesInDescription(): bool;

    public function isIncludeUnusedDirectiveDefinitions(): bool;

    /**
     * If `false` types will be printed in the original order if `true` they
     * will be sorted by name.
     */
    public function isNormalizeTypes(): bool;

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
     * If `false` directive definitions will be printed in the original order if
     * `true` they will be sorted by name.
     */
    public function isNormalizeDirectiveDefinitions(): bool;

    /**
     * Used to determine should the directive included in output or not.
     */
    public function getDirectiveFilter(): ?DirectiveFilter;

    /**
     * @return Closure():bool|null
     */
    public function getDirectivesDefinitionsFilter(): ?Closure;
}
