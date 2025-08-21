<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Docs\Examples;

use LastDragon_ru\GlobMatcher\BraceExpander\BraceExpander;
use LastDragon_ru\LaraASP\Dev\App\Example;

use function iterator_to_array;

$expander = new BraceExpander('{a,{0..10..2},c}.txt');

Example::dump(iterator_to_array($expander));
Example::dump($expander->node);
