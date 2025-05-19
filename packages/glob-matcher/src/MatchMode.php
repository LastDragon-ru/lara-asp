<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher;

enum MatchMode {
    case Match;
    case Starts;
    case Ends;
    case Contains;
}
