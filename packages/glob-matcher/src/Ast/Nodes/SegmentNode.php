<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\GlobMatcher\Ast\Node;
use LastDragon_ru\GlobMatcher\Options;
use Override;

use function preg_quote;

class SegmentNode implements Node, GlobNodeChild {
    public function __construct() {
        // empty
    }

    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        return preg_quote('/');
    }
}
