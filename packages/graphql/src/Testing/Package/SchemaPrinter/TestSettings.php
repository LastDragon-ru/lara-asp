<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing\Package\SchemaPrinter;

use Closure;
use GraphQL\Language\AST\DirectiveNode;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\DirectiveFilter;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Settings as SettingsContract;

class TestSettings implements SettingsContract {
    /**
     * @var array<string,mixed>
     */
    private array $settings = [
        'Space'                          => ' ',
        'Indent'                         => '    ',
        'LineEnd'                        => "\n",
        'LineLength'                     => 80,
        'NormalizeEnums'                 => false,
        'NormalizeUnions'                => false,
        'NormalizeFields'                => false,
        'NormalizeArguments'             => false,
        'NormalizeInterfaces'            => false,
        'NormalizeDescription'           => false,
        'NormalizeDirectiveLocations'    => false,
        'IncludeDirectives'              => false,
        'IncludeDirectivesInDescription' => false,
        'DirectiveFilter'                => null,
    ];

    public function __construct() {
        // empty
    }

    public function getSpace(): string {
        return $this->get('Space');
    }

    public function setSpace(string $value): static {
        return $this->set('Space', $value);
    }

    public function getIndent(): string {
        return $this->get('Indent');
    }

    public function setIndent(string $value): static {
        return $this->set('Indent', $value);
    }

    public function getFileEnd(): string {
        return $this->get('FileEnd');
    }

    public function setFileEnd(string $value): static {
        return $this->set('FileEnd', $value);
    }

    public function getLineEnd(): string {
        return $this->get('LineEnd');
    }

    public function setLineEnd(string $value): static {
        return $this->set('LineEnd', $value);
    }

    public function getLineLength(): int {
        return $this->get('LineLength');
    }

    public function setLineLength(int $value): static {
        return $this->set('LineLength', $value);
    }

    public function isIncludeUnusedTypeDefinitions(): bool {
        return $this->get('IncludeUnusedTypeDefinitions');
    }

    public function setIncludeUnusedTypeDefinitions(bool $value): static {
        return $this->set('IncludeUnusedTypeDefinitions', $value);
    }

    public function isIncludeDirectives(): bool {
        return $this->get('IncludeDirectives');
    }

    public function setIncludeDirectives(bool $value): static {
        return $this->set('IncludeDirectives', $value);
    }

    public function isIncludeDirectivesInDescription(): bool {
        return $this->get('IncludeDirectivesInDescription');
    }

    public function setIncludeDirectivesInDescription(bool $value): static {
        return $this->set('IncludeDirectivesInDescription', $value);
    }

    public function isIncludeUnusedDirectiveDefinitions(): bool {
        return $this->get('IncludeUnusedDirectiveDefinitions');
    }

    public function setIncludeUnusedDirectiveDefinitions(bool $value): static {
        return $this->set('IncludeUnusedDirectiveDefinitions', $value);
    }

    public function isNormalizeSchema(): bool {
        return $this->get('NormalizeSchema');
    }

    public function setNormalizeSchema(bool $value): static {
        return $this->set('NormalizeSchema', $value);
    }

    public function isNormalizeUnions(): bool {
        return $this->get('NormalizeUnions');
    }

    public function setNormalizeUnions(bool $value): static {
        return $this->set('NormalizeUnions', $value);
    }

    public function isNormalizeEnums(): bool {
        return $this->get('NormalizeEnums');
    }

    public function setNormalizeEnums(bool $value): static {
        return $this->set('NormalizeEnums', $value);
    }

    public function isNormalizeInterfaces(): bool {
        return $this->get('NormalizeInterfaces');
    }

    public function setNormalizeInterfaces(bool $value): static {
        return $this->set('NormalizeInterfaces', $value);
    }

    public function isNormalizeFields(): bool {
        return $this->get('NormalizeFields');
    }

    public function setNormalizeFields(bool $value): static {
        return $this->set('NormalizeFields', $value);
    }

    public function isNormalizeArguments(): bool {
        return $this->get('NormalizeArguments');
    }

    public function setNormalizeArguments(bool $value): static {
        return $this->set('NormalizeArguments', $value);
    }

    public function isNormalizeDescription(): bool {
        return $this->get('NormalizeDescription');
    }

    public function setNormalizeDescription(bool $value): static {
        return $this->set('NormalizeDescription', $value);
    }

    public function isNormalizeDirectiveLocations(): bool {
        return $this->get('NormalizeDirectiveLocations');
    }

    public function setNormalizeDirectiveLocations(bool $value): static {
        return $this->set('NormalizeDirectiveLocations', $value);
    }

    public function getDirectiveFilter(): ?DirectiveFilter {
        return $this->get('DirectiveFilter');
    }

    /**
     * @param DirectiveFilter|Closure(DirectiveNode):bool|null $value
     */
    public function setDirectiveFilter(DirectiveFilter|Closure|null $value): static {
        if ($value instanceof Closure) {
            $value = new class($value) implements DirectiveFilter {
                public function __construct(
                    protected Closure $filter,
                ) {
                    // empty
                }

                public function isAllowedDirective(DirectiveNode $directive): bool {
                    return ($this->filter)($directive);
                }
            };
        }

        return $this->set('DirectiveFilter', $value);
    }

    protected function get(string $setting): mixed {
        return $this->settings[$setting];
    }

    protected function set(string $setting, mixed $value): static {
        $settings                     = clone $this;
        $settings->settings[$setting] = $value;

        return $settings;
    }
}
