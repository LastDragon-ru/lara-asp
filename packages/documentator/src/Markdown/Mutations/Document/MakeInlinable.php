<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use Generator;
use IteratorAggregate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Footnote\Prefix as FootnotesPrefix;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Footnote\RemoveUnused as FootnotesRemoveUnused;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference\Prefix as ReferencesPrefix;
use League\CommonMark\Node\Node;
use Override;

/**
 * Renames all references/footnotes/etc to make possible inline the
 * document into another document without conflicts/ambiguities.
 *
 * @implements IteratorAggregate<array-key, Mutation<covariant Node>>
 */
readonly class MakeInlinable implements IteratorAggregate {
    public function __construct(
        protected string $prefix,
    ) {
        // empty
    }

    /**
     * @return Generator<array-key, Mutation<covariant Node>>
     */
    #[Override]
    public function getIterator(): Generator {
        yield from [
            new FootnotesRemoveUnused(),
            new FootnotesPrefix($this->prefix),
            new ReferencesPrefix($this->prefix),
        ];
    }
}
