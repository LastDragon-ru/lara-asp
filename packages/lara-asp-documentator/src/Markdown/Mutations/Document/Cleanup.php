<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use Generator;
use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Comment\Remove as CommentsRemove;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Footnote\RemoveUnused as FootnotesRemoveUnused;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference\RemoveUnused as ReferencesRemoveUnused;
use League\CommonMark\Node\Node;
use Override;

/**
 * Removes all nodes that are not used/visible (eg comments, unused footnotes
 * /references/, etc).
 *
 * Please note, single pass cannot delete some circular structures, see tests
 * for more details.
 *
 * @implements IteratorAggregate<array-key, Mutation<covariant Node>>
 */
readonly class Cleanup implements IteratorAggregate {
    public function __construct() {
        // empty
    }

    /**
     * @return Generator<array-key, Mutation<covariant Node>>
     */
    #[Override]
    public function getIterator(): Generator {
        yield from [
            new FootnotesRemoveUnused(),
            new ReferencesRemoveUnused(),
            new CommentsRemove(),
        ];
    }
}
