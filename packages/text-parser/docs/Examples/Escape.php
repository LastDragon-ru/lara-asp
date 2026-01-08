<?php declare(strict_types = 1);

namespace LastDragon_ru\TextParser\Docs\Examples;

use LastDragon_ru\LaraASP\Dev\App\Example;
use LastDragon_ru\TextParser\Tokenizer\Tokenizer;

use function iterator_to_array;

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

enum Name: string {
    case Slash     = '/';
    case Backslash = '\\';
}

$input     = 'a/b\\/\\c';
$tokenizer = new Tokenizer(Name::class, Name::Backslash);
$tokens    = $tokenizer->tokenize([$input]);

Example::dump(iterator_to_array($tokens));
