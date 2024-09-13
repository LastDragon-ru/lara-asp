<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\CodeLinks\Contracts\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code as CodeNode;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link as LinkNode;

class LinkToken {
    public function __construct(
        public readonly Link $link,
        public bool $deprecated,
        /**
         * @var non-empty-list<LinkNode|CodeNode>
         */
        public array $nodes,
    ) {
        // empty
    }
}
