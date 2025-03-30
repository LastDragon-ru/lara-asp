<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Extract;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use Override;

/**
 * Extract text.
 *
 * @implements Mutation<DocumentNode>
 */
readonly class Text implements Mutation {
    public function __construct(
        protected Location $location,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function nodes(): array {
        return [
            DocumentNode::class,
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function mutagens(Document $document, Node $node): array {
        return [
            new Extract($this->location),
        ];
    }
}
