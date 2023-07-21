<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Testing;

use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use SplFileInfo;

/**
 * @deprecated 4.4.0 Please use {@see GraphQLExpected} instead.
 */
class GraphQLExpectedType extends GraphQLExpected {
    /**
     * @inheritDoc
     */
    public function __construct(
        protected Type|SplFileInfo|string $type,
        ?array $usedTypes = null,
        ?array $usedDirectives = null,
        ?Settings $settings = null,
    ) {
        parent::__construct($this->type, $usedTypes, $usedDirectives, $settings);
    }

    public function getType(): Type|SplFileInfo|string {
        return $this->type;
    }
}
