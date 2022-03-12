<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedSchema;
use SplFileInfo;

class GraphQLExpectedSchema {
    /**
     * @param array<string>|null $usedTypes
     * @param array<string>|null $unusedTypes
     * @param array<string>|null $usedDirectives
     * @param array<string>|null $unusedDirectives
     */
    public function __construct(
        protected PrintedSchema|Schema|SplFileInfo|string $schema,
        protected ?array $usedTypes = null,
        protected ?array $unusedTypes = null,
        protected ?array $usedDirectives = null,
        protected ?array $unusedDirectives = null,
    ) {
        // empty
    }

    public function getSchema(): PrintedSchema|Schema|SplFileInfo|string {
        return $this->schema;
    }

    /**
     * @return array<string>|null
     */
    public function getUsedTypes(): ?array {
        return $this->usedTypes;
    }

    /**
     * @param array<string>|null $usedTypes
     */
    public function setUsedTypes(?array $usedTypes): static {
        $this->usedTypes = $usedTypes;

        return $this;
    }

    /**
     * @return array<string>|null
     */
    public function getUnusedTypes(): ?array {
        return $this->unusedTypes;
    }

    /**
     * @param array<string>|null $unusedTypes
     */
    public function setUnusedTypes(?array $unusedTypes): static {
        $this->unusedTypes = $unusedTypes;

        return $this;
    }

    /**
     * @return array<string>|null
     */
    public function getUsedDirectives(): ?array {
        return $this->usedDirectives;
    }

    /**
     * @param array<string>|null $usedDirectives
     */
    public function setUsedDirectives(?array $usedDirectives): static {
        $this->usedDirectives = $usedDirectives;

        return $this;
    }

    /**
     * @return array<string>|null
     */
    public function getUnusedDirectives(): ?array {
        return $this->unusedDirectives;
    }

    /**
     * @param array<string>|null $unusedDirectives
     */
    public function setUnusedDirectives(?array $unusedDirectives): static {
        $this->unusedDirectives = $unusedDirectives;

        return $this;
    }
}
