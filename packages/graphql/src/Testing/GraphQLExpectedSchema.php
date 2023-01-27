<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Type\Schema;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Result;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use SplFileInfo;

class GraphQLExpectedSchema extends GraphQLExpected {
    /**
     * @inheritDoc
     */
    public function __construct(
        protected Result|Schema|SplFileInfo|string $schema,
        ?array $usedTypes = null,
        ?array $usedDirectives = null,
        ?Settings $settings = null,
    ) {
        parent::__construct($usedTypes, $usedDirectives, $settings);
    }

    public function getSchema(): Result|Schema|SplFileInfo|string {
        return $this->schema;
    }
}
