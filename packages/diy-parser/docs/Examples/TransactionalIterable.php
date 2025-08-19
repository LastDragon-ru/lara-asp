<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Docs\Examples;

use LastDragon_ru\DiyParser\Docs\Calculator\Name;
use LastDragon_ru\DiyParser\Iterables\TransactionalIterable;
use LastDragon_ru\DiyParser\Tokenizer\Tokenizer;
use LastDragon_ru\LaraASP\Dev\App\Example;

$input    = '1 + 2';
$tokens   = (new Tokenizer(Name::class))->tokenize([$input]);
$iterable = new TransactionalIterable($tokens, 5, 5);

Example::dump($iterable[0]);
Example::dump($iterable[4]);

$iterable->next(2);
$iterable->begin();     // start nested

Example::dump($iterable[-2]);
Example::dump($iterable[0]);
Example::dump($iterable[2]);

$iterable->rollback();  // oops

Example::dump($iterable[0]);
