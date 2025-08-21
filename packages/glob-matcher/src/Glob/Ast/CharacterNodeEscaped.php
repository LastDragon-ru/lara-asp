<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

enum CharacterNodeEscaped: string {
    case Colon              = ':';
    case Backslash          = '\\';
    case Circumflex         = '^';
    case LeftSquareBracket  = '[';
    case RightSquareBracket = ']';
}
