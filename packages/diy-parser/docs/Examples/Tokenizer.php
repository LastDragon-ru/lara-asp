<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Docs\Examples;

use LastDragon_ru\DiyParser\Docs\Calculator\Name;
use LastDragon_ru\DiyParser\Tokenizer\Tokenizer;
use LastDragon_ru\LaraASP\Dev\App\Example;

use function iterator_to_array;

$input     = '2 - (1 + 2) / 3';
$tokenizer = new Tokenizer(Name::class);
$tokens    = $tokenizer->tokenize([$input]);

Example::dump(iterator_to_array($tokens));
