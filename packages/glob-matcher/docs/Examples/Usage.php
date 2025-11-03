<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Docs\Examples;

use LastDragon_ru\GlobMatcher\GlobMatcher;
use LastDragon_ru\GlobMatcher\Options;
use LastDragon_ru\LaraASP\Dev\App\Example;

// Full-featured
$fullGlob = new GlobMatcher('/**/{a,b,c}.txt');

Example::dump($fullGlob->match('/a.txt'));
Example::dump($fullGlob->match('/a/b/c.txt'));
Example::dump($fullGlob->match('/a/b/d.txt'));

// Without `globstar`
$noGlobstar = new GlobMatcher('/**/{a,b,c}.txt', new Options(globstar: false));

Example::dump($noGlobstar->match('/a.txt'));
Example::dump($noGlobstar->match('/**/a.txt'));

// Escaping
$escaped = new GlobMatcher('/\\*.txt');

Example::dump($escaped->match('/a.txt'));
Example::dump($escaped->match('/*.txt'));
