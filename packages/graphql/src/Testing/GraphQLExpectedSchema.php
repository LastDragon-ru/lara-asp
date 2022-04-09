<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedSchema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings;
use SplFileInfo;

class GraphQLExpectedSchema extends GraphQLExpected {
    /**
     * @inheritDoc
     *
     * @param array<string>|null $unusedTypes
     * @param array<string>|null $unusedDirectives
     */
    public function __construct(
        protected PrintedSchema|Schema|SplFileInfo|string $schema,
        ?array $usedTypes = null,
        protected ?array $unusedTypes = null,
        ?array $usedDirectives = null,
        protected ?array $unusedDirectives = null,
        ?Settings $settings = null,
    ) {
        parent::__construct($usedTypes, $usedDirectives, $settings);
    }

    public function getSchema(): PrintedSchema|Schema|SplFileInfo|string {
        return $this->schema;
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
