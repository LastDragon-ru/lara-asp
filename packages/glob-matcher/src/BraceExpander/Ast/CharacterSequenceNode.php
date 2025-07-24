<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Ast;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\DiyParser\Package as ParserPackage;
use Override;

use function abs;
use function mb_chr;
use function mb_ord;

class CharacterSequenceNode extends IncrementalSequenceNode {
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $start,
        /**
         * @var non-empty-string
         */
        public string $end,
        public ?int $increment = null,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public static function toIterable(Cursor $cursor): iterable {
        $start = mb_ord($cursor->node->start, ParserPackage::Encoding);
        $end   = mb_ord($cursor->node->end, ParserPackage::Encoding);
        $inc   = abs($cursor->node->increment ?? 1);
        $inc   = $start < $end ? $inc : -$inc;
        $steps = abs(($end - $start) / $inc);

        for ($code = $start, $step = 0; $step <= $steps; $step++, $code += $inc) {
            yield mb_chr($code, ParserPackage::Encoding);
        }
    }
}
