<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\DiyParser\Iterables\StringifyIterable;
use LastDragon_ru\DiyParser\Iterables\TokenEscapeIterable;
use LastDragon_ru\DiyParser\Tokenizer\Tokenizer;
use LastDragon_ru\DiyParser\Utils;
use LastDragon_ru\GlobMatcher\Ast\ParentNode;
use LastDragon_ru\GlobMatcher\Options;
use Override;

use function mb_strpos;

/**
 * @extends ParentNode<CharacterNodeChild>
 */
class CharacterNode extends ParentNode implements NameNodeChild {
    /**
     * @param list<CharacterNodeChild> $children
     */
    public function __construct(
        public bool $negated,
        array $children,
    ) {
        parent::__construct($children);
    }

    #[Override]
    public static function toRegex(Options $options, Cursor $cursor): string {
        $regex = '';

        foreach ($cursor as $child) {
            $regex .= match (true) {
                $child->node instanceof StringNode => self::escape($child->node->string),
                default                            => $child->node::toRegex($options, $child),
            };
        }

        if ($cursor->node->negated && mb_strpos($regex, '/') === false) {
            $regex .= '/';
        }

        return '['.($cursor->node->negated ? '^' : '').$regex.']';
    }

    private static function escape(string $string): string {
        $iterable = (new Tokenizer(CharacterNodeEscaped::class))->tokenize([$string]);
        $iterable = new TokenEscapeIterable($iterable, CharacterNodeEscaped::Backslash);
        $iterable = new StringifyIterable($iterable);
        $result   = Utils::toString($iterable);

        return $result;
    }
}
