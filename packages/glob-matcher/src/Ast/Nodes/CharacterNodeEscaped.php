<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Ast\Nodes;

enum CharacterNodeEscaped: string {
    case String             = '';
    case Colon              = ':';
    case Backslash          = '\\';
    case Circumflex         = '^';
    case LeftSquareBracket  = '[';
    case RightSquareBracket = ']';
}
