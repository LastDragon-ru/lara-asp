<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Testing;

use GraphQL\Language\AST\Node;
use LastDragon_ru\LaraASP\GraphQLPrinter\Contracts\Settings;
use SplFileInfo;

/**
 * @deprecated 4.4.0 Please use {@see GraphQLExpected} instead.
 */
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
        parent::__construct($this->node, $usedTypes, $usedDirectives, $settings);
    }

    public function getNode(): Node|SplFileInfo|string {
        return $this->node;
    }
}
