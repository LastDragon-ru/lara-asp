<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Calculator;

enum Name: string {
    case Plus             = '+';
    case Minus            = '-';
    case Asterisk         = '*';
    case Slash            = '/';
    case Space            = ' ';
    case LeftParenthesis  = '(';
    case RightParenthesis = ')';
}
