<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\GraphQL;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\Argument;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\EnumValueDefinition;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use LastDragon_ru\GraphQLPrinter\Contracts\Settings;
use SplFileInfo;

class Expected {
    /**
     * @param list<string>|null $usedTypes
     * @param list<string>|null $usedDirectives
     */
    public function __construct(
        protected Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|SplFileInfo|string $printable,
        protected ?array $usedTypes = null,
        protected ?array $usedDirectives = null,
        protected ?Settings $settings = null,
        protected ?Schema $schema = null,
    ) {
        // empty
    }

    public function getPrintable(): Node|Type|Directive|FieldDefinition|Argument|EnumValueDefinition|InputObjectField|Schema|SplFileInfo|string {
        return $this->printable;
    }

    /**
     * @return list<string>|null
     */
    public function getUsedTypes(): ?array {
        return $this->usedTypes;
    }

    /**
     * @param list<string>|null $usedTypes
     */
    public function setUsedTypes(?array $usedTypes): static {
        $this->usedTypes = $usedTypes;

        return $this;
    }

    /**
     * @return list<string>|null
     */
    public function getUsedDirectives(): ?array {
        return $this->usedDirectives;
    }

    /**
     * @param list<string>|null $usedDirectives
     */
    public function setUsedDirectives(?array $usedDirectives): static {
        $this->usedDirectives = $usedDirectives;

        return $this;
    }

    public function getSettings(): ?Settings {
        return $this->settings;
    }

    public function setSettings(?Settings $settings): static {
        $this->settings = $settings;

        return $this;
    }

    public function getSchema(): ?Schema {
        return $this->schema;
    }

    public function setSchema(?Schema $schema): static {
        $this->schema = $schema;

        return $this;
    }
}
