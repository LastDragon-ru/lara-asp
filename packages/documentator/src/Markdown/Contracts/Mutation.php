<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Contracts;

use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Delete;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Extract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Finalize;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutator\Mutagens\Replace;
use League\CommonMark\Node\Node;

/**
 * Must not modify original {@see Document}/{@see Node}.
 *
 * @template TNode of Node
 */
interface Mutation {
    /**
     * @return list<class-string<TNode>>
     */
    public static function nodes(): array;

    /**
     * @param TNode $node
     *
     * @return list<Replace|Delete|Extract|Finalize>
     */
    public function mutagens(Document $document, Node $node): array;
}
