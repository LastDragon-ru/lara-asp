<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Glob\Ast;

enum CharacterClass: string {
    case Alnum  = 'alnum';
    case Alpha  = 'alpha';
    case Ascii  = 'ascii';
    case Blank  = 'blank';
    case Cntrl  = 'cntrl';
    case Digit  = 'digit';
    case Graph  = 'graph';
    case Lower  = 'lower';
    case Print  = 'print';
    case Punct  = 'punct';
    case Space  = 'space';
    case Upper  = 'upper';
    case Word   = 'word';
    case Xdigit = 'xdigit';
}
