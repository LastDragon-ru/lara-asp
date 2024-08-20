<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Generated;

use League\CommonMark\Node\Block\AbstractBlock;

/**
 * Represents the generated text inside document.
 *
 * ```
 * [//]: # (start: <id>)
 *
 * ... text ...
 *
 * [//]: # (end: <id>)
 * ```
 */
class Block extends AbstractBlock {
    public function __construct(
        public readonly string $id,
    ) {
        parent::__construct();
    }
}
