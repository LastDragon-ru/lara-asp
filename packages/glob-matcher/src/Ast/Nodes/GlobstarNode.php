<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\DiyParser\Ast\NodeMergeable;
use LastDragon_ru\GlobMatcher\Ast\Node;
use LastDragon_ru\GlobMatcher\Options;
use Override;

use function str_replace;

class GlobstarNode implements Node, GlobNodeChild, NodeMergeable {
    public function __construct(
        /**
         * @var positive-int
         */
        public int $count = 1,
    ) {
        // empty
    }

    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        $mark  = 'globstar';
        $name  = NameNode::toRegex($options, new Cursor(new NameNode([new StringNode($mark)])));
        $name  = str_replace(["(?:{$mark})", $mark], '', $name);
        $regex = "(?:(?<=^|/)(?:{$name}[^/]*?)(?:(?:/|$)|(?=/|$)))*?";

        return $regex;
    }

    #[Override]
    public static function merge(NodeMergeable $previous, NodeMergeable $current): NodeMergeable {
        if ($previous::class === $current::class) {
            $previous->count = $previous->count + $current->count;
            $current         = $previous;
        }

        return $current;
    }
}
