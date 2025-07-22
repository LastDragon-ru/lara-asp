<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\BraceExpander\Parser;

enum Name: string {
    case Comma             = ',';
    case DoubleDot         = '..';
    case Backslash         = '\\';
    case LeftCurlyBracket  = '{';
    case RightCurlyBracket = '}';
}
