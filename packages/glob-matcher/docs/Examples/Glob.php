<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Docs\Examples;

use LastDragon_ru\GlobMatcher\Glob\Glob;
use LastDragon_ru\LaraASP\Dev\App\Example;

$glob = new Glob('/**/**/?.txt');

Example::dump((string) $glob->regex);
Example::dump($glob->node);
