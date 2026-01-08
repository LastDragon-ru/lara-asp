<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

use LastDragon_ru\GlobMatcher\Glob\Options;
use LastDragon_ru\GlobMatcher\Package;
use LastDragon_ru\TextParser\Ast\Cursor;
use LastDragon_ru\TextParser\Iterables\StringifyIterable;
use LastDragon_ru\TextParser\Iterables\TokenEscapeIterable;
use LastDragon_ru\TextParser\Tokenizer\Tokenizer;
use LastDragon_ru\TextParser\Utils;
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

        if ($cursor->node->negated && mb_strpos($regex, '/', encoding: Package::Encoding) === false) {
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
