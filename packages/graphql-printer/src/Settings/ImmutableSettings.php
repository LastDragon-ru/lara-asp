<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Settings;

use Closure;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\TypeFilter;
use Override;

abstract class ImmutableSettings implements Settings {
    protected string           $space                             = ' ';
    protected string           $indent                            = '    ';
    protected string           $fileEnd                           = "\n";
    protected string           $lineEnd                           = "\n";
    protected int              $lineLength                        = 80;
    protected bool             $printDirectives                   = true;
    protected bool             $printDirectiveDefinitions         = true;
    protected bool             $printUnusedDefinitions            = false;
    protected bool             $normalizeDefinitions              = true;
    protected bool             $normalizeUnions                   = true;
    protected bool             $normalizeEnums                    = true;
    protected bool             $normalizeInterfaces               = true;
    protected bool             $normalizeFields                   = true;
    protected bool             $normalizeArguments                = false;
    protected bool             $normalizeDescription              = true;
    protected bool             $normalizeDirectives               = false;
    protected bool             $normalizeDirectiveLocations       = true;
    protected bool             $alwaysMultilineUnions             = true;
    protected bool             $alwaysMultilineArguments          = true;
    protected bool             $alwaysMultilineInterfaces         = true;
    protected bool             $alwaysMultilineDirectives         = true;
    protected bool             $alwaysMultilineDirectiveLocations = true;
    protected ?TypeFilter      $typeFilter                        = null;
    protected ?TypeFilter      $typeDefinitionFilter              = null;
    protected ?DirectiveFilter $directiveFilter                   = null;
    protected ?DirectiveFilter $directiveDefinitionFilter         = null;

    public function __construct() {
        // empty
    }

    #[Override]
    public function getSpace(): string {
        return $this->space;
    }

    public function setSpace(string $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->space = $value;
        });
    }

    #[Override]
    public function getIndent(): string {
        return $this->indent;
    }

    public function setIndent(string $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->indent = $value;
        });
    }

    #[Override]
    public function getFileEnd(): string {
        return $this->fileEnd;
    }

    public function setFileEnd(string $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->fileEnd = $value;
        });
    }

    #[Override]
    public function getLineEnd(): string {
        return $this->lineEnd;
    }

    public function setLineEnd(string $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->lineEnd = $value;
        });
    }

    #[Override]
    public function getLineLength(): int {
        return $this->lineLength;
    }

    public function setLineLength(int $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->lineLength = $value;
        });
    }

    #[Override]
    public function isPrintUnusedDefinitions(): bool {
        return $this->printUnusedDefinitions;
    }

    public function setPrintUnusedDefinitions(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->printUnusedDefinitions = $value;
        });
    }

    #[Override]
    public function isPrintDirectives(): bool {
        return $this->printDirectives;
    }

    public function setPrintDirectives(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->printDirectives = $value;
        });
    }

    #[Override]
    public function isPrintDirectiveDefinitions(): bool {
        return $this->printDirectiveDefinitions;
    }

    public function setPrintDirectiveDefinitions(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->printDirectiveDefinitions = $value;
        });
    }

    #[Override]
    public function isNormalizeDefinitions(): bool {
        return $this->normalizeDefinitions;
    }

    public function setNormalizeDefinitions(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeDefinitions = $value;
        });
    }

    #[Override]
    public function isNormalizeUnions(): bool {
        return $this->normalizeUnions;
    }

    public function setNormalizeUnions(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeUnions = $value;
        });
    }

    #[Override]
    public function isNormalizeEnums(): bool {
        return $this->normalizeEnums;
    }

    public function setNormalizeEnums(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeEnums = $value;
        });
    }

    #[Override]
    public function isNormalizeInterfaces(): bool {
        return $this->normalizeInterfaces;
    }

    public function setNormalizeInterfaces(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeInterfaces = $value;
        });
    }

    #[Override]
    public function isNormalizeFields(): bool {
        return $this->normalizeFields;
    }

    public function setNormalizeFields(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeFields = $value;
        });
    }

    #[Override]
    public function isNormalizeArguments(): bool {
        return $this->normalizeArguments;
    }

    public function setNormalizeArguments(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeArguments = $value;
        });
    }

    #[Override]
    public function isNormalizeDescription(): bool {
        return $this->normalizeDescription;
    }

    public function setNormalizeDescription(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeDescription = $value;
        });
    }

    #[Override]
    public function isNormalizeDirectives(): bool {
        return $this->normalizeDirectives;
    }

    public function setNormalizeDirectives(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeDirectives = $value;
        });
    }

    #[Override]
    public function isNormalizeDirectiveLocations(): bool {
        return $this->normalizeDirectiveLocations;
    }

    public function setNormalizeDirectiveLocations(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->normalizeDirectiveLocations = $value;
        });
    }

    #[Override]
    public function isAlwaysMultilineUnions(): bool {
        return $this->alwaysMultilineUnions;
    }

    public function setAlwaysMultilineUnions(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->alwaysMultilineUnions = $value;
        });
    }

    #[Override]
    public function isAlwaysMultilineArguments(): bool {
        return $this->alwaysMultilineArguments;
    }

    public function setAlwaysMultilineArguments(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->alwaysMultilineArguments = $value;
        });
    }

    #[Override]
    public function isAlwaysMultilineInterfaces(): bool {
        return $this->alwaysMultilineInterfaces;
    }

    public function setAlwaysMultilineInterfaces(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->alwaysMultilineInterfaces = $value;
        });
    }

    #[Override]
    public function isAlwaysMultilineDirectives(): bool {
        return $this->alwaysMultilineDirectives;
    }

    public function setAlwaysMultilineDirectives(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->alwaysMultilineDirectives = $value;
        });
    }

    #[Override]
    public function isAlwaysMultilineDirectiveLocations(): bool {
        return $this->alwaysMultilineDirectiveLocations;
    }

    public function setAlwaysMultilineDirectiveLocations(bool $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->alwaysMultilineDirectiveLocations = $value;
        });
    }

    #[Override]
    public function getDirectiveFilter(): ?DirectiveFilter {
        return $this->directiveFilter;
    }

    public function setDirectiveFilter(?DirectiveFilter $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->directiveFilter = $value;
        });
    }

    #[Override]
    public function getTypeFilter(): ?TypeFilter {
        return $this->typeFilter;
    }

    public function setTypeFilter(?TypeFilter $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->typeFilter = $value;
        });
    }

    #[Override]
    public function getTypeDefinitionFilter(): ?TypeFilter {
        return $this->typeDefinitionFilter;
    }

    public function setTypeDefinitionFilter(?TypeFilter $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->typeDefinitionFilter = $value;
        });
    }

    #[Override]
    public function getDirectiveDefinitionFilter(): ?DirectiveFilter {
        return $this->directiveDefinitionFilter;
    }

    public function setDirectiveDefinitionFilter(?DirectiveFilter $value): static {
        return $this->set(static function (self $settings) use ($value): void {
            $settings->directiveDefinitionFilter = $value;
        });
    }

    /**
     * @param Closure(static): void $callback
     */
    protected function set(Closure $callback): static {
        $settings = clone $this;

        $callback($settings);

        return $settings;
    }

    public static function createFrom(Settings $settings): self {
        return (new class() extends ImmutableSettings {
            // empty
        })
            ->setSpace($settings->getSpace())
            ->setIndent($settings->getIndent())
            ->setFileEnd($settings->getFileEnd())
            ->setLineEnd($settings->getLineEnd())
            ->setLineLength($settings->getLineLength())
            ->setPrintDirectives($settings->isPrintDirectives())
            ->setPrintDirectiveDefinitions($settings->isPrintDirectiveDefinitions())
            ->setPrintUnusedDefinitions($settings->isPrintUnusedDefinitions())
            ->setNormalizeDefinitions($settings->isNormalizeDefinitions())
            ->setNormalizeUnions($settings->isNormalizeUnions())
            ->setNormalizeEnums($settings->isNormalizeEnums())
            ->setNormalizeInterfaces($settings->isNormalizeInterfaces())
            ->setNormalizeFields($settings->isNormalizeFields())
            ->setNormalizeArguments($settings->isNormalizeArguments())
            ->setNormalizeDescription($settings->isNormalizeDescription())
            ->setNormalizeDirectives($settings->isNormalizeDirectives())
            ->setNormalizeDirectiveLocations($settings->isNormalizeDirectiveLocations())
            ->setAlwaysMultilineUnions($settings->isAlwaysMultilineUnions())
            ->setAlwaysMultilineArguments($settings->isAlwaysMultilineArguments())
            ->setAlwaysMultilineInterfaces($settings->isAlwaysMultilineInterfaces())
            ->setAlwaysMultilineDirectives($settings->isAlwaysMultilineDirectives())
            ->setAlwaysMultilineDirectiveLocations($settings->isAlwaysMultilineDirectiveLocations())
            ->setTypeFilter($settings->getTypeFilter())
            ->setTypeDefinitionFilter($settings->getTypeDefinitionFilter())
            ->setDirectiveDefinitionFilter($settings->getDirectiveDefinitionFilter())
            ->setDirectiveFilter($settings->getDirectiveFilter());
    }
}
