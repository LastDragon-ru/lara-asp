<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Testing;

use GraphQL\Language\AST\Node;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use SplFileInfo;

class GraphQLExpectedNode extends GraphQLExpected {
    /**
     * @inheritDoc
     */
    public function __construct(
        protected Node|SplFileInfo|string $node,
        ?array $usedTypes = null,
        ?array $usedDirectives = null,
        ?Settings $settings = null,
    ) {
        parent::__construct($usedTypes, $usedDirectives, $settings);
    }

    public function getNode(): Node|SplFileInfo|string {
        return $this->node;
    }
}
