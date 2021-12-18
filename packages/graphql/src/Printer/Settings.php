<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer;

use Closure;

// TODO: Directive resolver
// TODO: Throw error if directive definition not found

interface Settings {
    public function getSpace(): string;

    public function getIndent(): string;

    public function getFileEnd(): string;

    public function getLineEnd(): string;

    public function getLineLength(): int;

    public function isIncludeUnusedTypes(): bool;

    public function isIncludeDirectives(): bool;

    public function isIncludeUnusedDirectivesDefinitions(): bool;

    /**
     * If `false` types will be printed in the original order if `true` they
     * will be sorted by name.
     */
    public function isNormalizeTypes(): bool;

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
     * If `false` directive will be printed in the original order if `true` they
     * will be sorted by name.
     */
    public function isNormalizeDirectives(): bool;

    /**
     * If `false` directive definitions will be printed in the original order if
     * `true` they will be sorted by name.
     */
    public function isNormalizeDirectivesDefinitions(): bool;

    /**
     * @return Closure():bool|null
     */
    public function getTypesFilter(): ?Closure;

    /**
     * @return Closure():bool|null
     */
    public function getDirectivesFilter(): ?Closure;

    /**
     * @return Closure():bool|null
     */
    public function getDirectivesDefinitionsFilter(): ?Closure;
}
