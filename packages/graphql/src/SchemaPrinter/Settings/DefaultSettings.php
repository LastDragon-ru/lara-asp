<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

class DefaultSettings implements Settings {
    public function __construct() {
        // empty
    }

    public function getSpace(): string {
        return ' ';
    }

    public function getIndent(): string {
        return '  ';
    }

    public function getFileEnd(): string {
        return "\n";
    }

    public function getLineEnd(): string {
        return "\n";
    }

    public function getLineLength(): int {
        return 80;
    }

    public function isIncludeUnusedTypeDefinitions(): bool {
        return false;
    }

    public function isIncludeDirectives(): bool {
        return false;
    }

    public function isIncludeDirectivesInDescription(): bool {
        return false;
    }

    public function isIncludeUnusedDirectiveDefinitions(): bool {
        return false;
    }

    public function isNormalizeSchema(): bool {
        return false;
    }

    public function isNormalizeUnions(): bool {
        return false;
    }

    public function isNormalizeEnums(): bool {
        return false;
    }

    public function isNormalizeInterfaces(): bool {
        return false;
    }

    public function isNormalizeFields(): bool {
        return false;
    }

    public function isNormalizeArguments(): bool {
        return false;
    }

    public function isNormalizeDescription(): bool {
        return false;
    }

    public function isNormalizeDirectiveLocations(): bool {
        return false;
    }

    public function getDirectiveFilter(): ?DirectiveFilter {
        return null;
    }
}
