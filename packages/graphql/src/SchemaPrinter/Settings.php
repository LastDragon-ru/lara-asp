<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\DirectiveFilter;

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
     * Used to determine should the directive included in output or not.
     */
    public function getDirectiveFilter(): ?DirectiveFilter;
}
