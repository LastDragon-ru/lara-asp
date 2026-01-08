<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Ast;

use Override;

class NodeString implements NodeMergeable {
    public function __construct(
        /**
         * @var non-empty-string
         */
        public string $string,
    ) {
        // empty
    }

    #[Override]
    public static function merge(NodeMergeable $previous, NodeMergeable $current): NodeMergeable {
        if ($previous::class === $current::class) {
            $previous->string = $previous->string.$current->string;
            $current          = $previous;
        }

        return $current;
    }
}
