<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Testing;

use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\PrintedType;
use LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Contracts\Settings;
use SplFileInfo;

class GraphQLExpectedType extends GraphQLExpected {
    /**
     * @inheritDoc
     */
    public function __construct(
        protected PrintedType|Type|SplFileInfo|string $type,
        ?array $usedTypes = null,
        ?array $usedDirectives = null,
        ?Settings $settings = null,
    ) {
        parent::__construct($usedTypes, $usedDirectives, $settings);
    }

    public function getType(): PrintedType|Type|SplFileInfo|string {
        return $this->type;
    }
}
