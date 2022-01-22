<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

use Closure;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings;

abstract class ImmutableSettings implements Settings {
    protected string           $space;
    protected string           $indent;
    protected string           $fileEnd;
    protected string           $lineEnd;
    protected int              $lineLength;
    protected bool             $includeUnusedTypeDefinitions;
    protected bool             $includeDirectives;
    protected bool             $includeDirectivesInDescription;
    protected bool             $includeUnusedDirectiveDefinitions;
    protected bool             $normalizeSchema;
    protected bool             $normalizeUnions;
    protected bool             $normalizeEnums;
    protected bool             $normalizeInterfaces;
    protected bool             $normalizeFields;
    protected bool             $normalizeArguments;
    protected bool             $normalizeDescription;
    protected bool             $normalizeDirectiveLocations;
    protected ?DirectiveFilter $directiveFilter;

    public function __construct() {
        // empty
    }

    public function getSpace(): string {
        return $this->space;
    }

    public function setSpace(string $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->space = $value;
        });
    }

    public function getIndent(): string {
        return $this->indent;
    }

    public function setIndent(string $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->indent = $value;
        });
    }

    public function getFileEnd(): string {
        return $this->fileEnd;
    }

    public function setFileEnd(string $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->fileEnd = $value;
        });
    }

    public function getLineEnd(): string {
        return $this->lineEnd;
    }

    public function setLineEnd(string $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->lineEnd = $value;
        });
    }

    public function getLineLength(): int {
        return $this->lineLength;
    }

    public function setLineLength(int $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->lineLength = $value;
        });
    }

    public function isIncludeUnusedTypeDefinitions(): bool {
        return $this->includeUnusedTypeDefinitions;
    }

    public function setIncludeUnusedTypeDefinitions(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->includeUnusedTypeDefinitions = $value;
        });
    }

    public function isIncludeDirectives(): bool {
        return $this->includeDirectives;
    }

    public function setIncludeDirectives(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->includeDirectives = $value;
        });
    }

    public function isIncludeDirectivesInDescription(): bool {
        return $this->includeDirectivesInDescription;
    }

    public function setIncludeDirectivesInDescription(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->includeDirectivesInDescription = $value;
        });
    }

    public function isIncludeUnusedDirectiveDefinitions(): bool {
        return $this->includeUnusedDirectiveDefinitions;
    }

    public function setIncludeUnusedDirectiveDefinitions(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->includeUnusedDirectiveDefinitions = $value;
        });
    }

    public function isNormalizeSchema(): bool {
        return $this->normalizeSchema;
    }

    public function setNormalizeSchema(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeSchema = $value;
        });
    }

    public function isNormalizeUnions(): bool {
        return $this->normalizeUnions;
    }

    public function setNormalizeUnions(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeUnions = $value;
        });
    }

    public function isNormalizeEnums(): bool {
        return $this->normalizeEnums;
    }

    public function setNormalizeEnums(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeEnums = $value;
        });
    }

    public function isNormalizeInterfaces(): bool {
        return $this->normalizeInterfaces;
    }

    public function setNormalizeInterfaces(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeInterfaces = $value;
        });
    }

    public function isNormalizeFields(): bool {
        return $this->normalizeFields;
    }

    public function setNormalizeFields(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeFields = $value;
        });
    }

    public function isNormalizeArguments(): bool {
        return $this->normalizeArguments;
    }

    public function setNormalizeArguments(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeArguments = $value;
        });
    }

    public function isNormalizeDescription(): bool {
        return $this->normalizeDescription;
    }

    public function setNormalizeDescription(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeDescription = $value;
        });
    }

    public function isNormalizeDirectiveLocations(): bool {
        return $this->normalizeDirectiveLocations;
    }

    public function setNormalizeDirectiveLocations(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeDirectiveLocations = $value;
        });
    }

    public function getDirectiveFilter(): ?DirectiveFilter {
        return $this->directiveFilter;
    }

    public function setDirectiveFilter(?DirectiveFilter $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->directiveFilter = $value;
        });
    }

    protected function set(Closure $callback): static {
        $settings = clone $this;

        $callback($settings);

        return $settings;
    }
}
