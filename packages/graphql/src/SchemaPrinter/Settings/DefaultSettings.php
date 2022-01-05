<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

class DefaultSettings implements Settings {
    public function __construct() {
        // empty
    }

    public function getSpace(): string {
        return ' ';
    }

    public function getIndent(): string {
        return '    ';
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

    public function isIncludeUnusedTypes(): bool {
        return false;
    }

    public function isIncludeDirectives(): bool {
        return true;
    }

    public function isIncludeUnusedDirectivesDefinitions(): bool {
        return false;
    }

    public function isNormalizeTypes(): bool {
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

    public function isNormalizeDirectives(): bool {
        return false;
    }

    public function isNormalizeDirectivesDefinitions(): bool {
        return false;
    }

    public function getTypesFilter(): ?Closure {
        return null;
    }

    public function getDirectivesFilter(): ?Closure {
        return null;
    }

    public function getDirectivesDefinitionsFilter(): ?Closure {
        return null;
    }
}
