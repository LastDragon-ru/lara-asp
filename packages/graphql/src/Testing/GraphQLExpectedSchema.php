<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedSchema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use SplFileInfo;

class GraphQLExpectedSchema extends GraphQLExpected {
    /**
     * @inheritDoc
     *
     * @param array<string>|null $unusedDirectives
     */
    public function __construct(
        protected PrintedSchema|Schema|SplFileInfo|string $schema,
        ?array $usedTypes = null,
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
