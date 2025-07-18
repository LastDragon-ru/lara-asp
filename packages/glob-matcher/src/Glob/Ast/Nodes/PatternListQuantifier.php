<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast\Nodes;

enum PatternListQuantifier {
    case ZeroOrOne;
    case ZeroOrMore;
    case OneOrMore;
    case OneOf;
    case Not;
}
