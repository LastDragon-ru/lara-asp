<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Parser;

enum Name: string {
    case Asterisk           = '*';
    case Question           = '?';
    case Slash              = '/';
    case Backslash          = '\\';
    case LeftSquareBracket  = '[';
    case RightSquareBracket = ']';
    case ExclamationMark    = '!';
    case Circumflex         = '^';
    case Colon              = ':';
    case Dot                = '.';
    case Equal              = '=';
    case Plus               = '+';
    case At                 = '@';
    case LeftParenthesis    = '(';
    case RightParenthesis   = ')';
    case VerticalLine       = '|';
}
