<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use Generator;
use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Content;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link\Unlink;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Extract;
use League\CommonMark\Node\Block\Document as DocumentNode;
use League\CommonMark\Node\Node;
use Override;

/**
 * @implements IteratorAggregate<array-key, Mutation<covariant Node>>
 */
readonly class Title implements IteratorAggregate {
    public function __construct() {
        // empty
    }

    /**
     * @return Generator<array-key, Mutation<covariant Node>>
     */
    #[Override]
    public function getIterator(): Generator {
        yield new readonly class() implements Mutation {
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
                // Exists?
                $heading = TitleData::get($node);

                if ($heading === null) {
                    return [
                        new Extract(new Location(0, 0, 0, 0)),
                    ];
                }

                // Return
                return [
                    new Extract(Content::get($heading)),
                ];
            }
        };

        yield from new MakeSplittable();
        yield new Unlink();
    }
}
