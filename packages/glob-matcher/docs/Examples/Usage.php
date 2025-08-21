<?php declare(strict_types = 1);

namespace LastDragon_ru\GlobMatcher\Docs\Examples;

use LastDragon_ru\GlobMatcher\GlobMatcher;
use LastDragon_ru\GlobMatcher\Options;
use LastDragon_ru\LaraASP\Dev\App\Example;

// Full-featured
$fullGlob = new GlobMatcher('/**/{a,b,c}.txt');

Example::dump($fullGlob->isMatch('/a.txt'));
Example::dump($fullGlob->isMatch('/a/b/c.txt'));
Example::dump($fullGlob->isMatch('/a/b/d.txt'));

// Without `globstar`
$noGlobstar = new GlobMatcher('/**/{a,b,c}.txt', new Options(globstar: false));

Example::dump($noGlobstar->isMatch('/a.txt'));
Example::dump($noGlobstar->isMatch('/**/a.txt'));

// Escaping
$escaped = new GlobMatcher('/\\*.txt');

Example::dump($escaped->isMatch('/a.txt'));
Example::dump($escaped->isMatch('/*.txt'));
