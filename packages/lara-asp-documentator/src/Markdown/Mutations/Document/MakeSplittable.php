<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use Generator;
use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Footnote\Remove as FootnotesRemove;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link\UnlinkToSelf as LinksUnlinkToSelf;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference\Inline as ReferencesInline;
use League\CommonMark\Node\Node;
use Override;

/**
 * Inlines all references, removes footnotes, etc, to make possible
 * extract any block/paragraph from the document without losing
 * information.
 *
 * @implements IteratorAggregate<array-key, Mutation<covariant Node>>
 */
readonly class MakeSplittable implements IteratorAggregate {
    public function __construct() {
        // empty
    }

    /**
     * @return Generator<array-key, Mutation<covariant Node>>
     */
    #[Override]
    public function getIterator(): Generator {
        yield from [
            new LinksUnlinkToSelf(),
            new FootnotesRemove(),
            new ReferencesInline(),
        ];
    }
}
