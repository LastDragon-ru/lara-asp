<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Docs\Examples;

use LastDragon_ru\DiyParser\Ast\Cursor;
use LastDragon_ru\DiyParser\Docs\Calculator\Ast\ExpressionNode;
use LastDragon_ru\DiyParser\Docs\Calculator\Parser;
use LastDragon_ru\LaraASP\Dev\App\Example;

use function assert;

// Parse
$ast = (new Parser())->parse('2 - (1 + 2) / 3');

assert($ast instanceof ExpressionNode);

// Create the cursor
$cursor = new Cursor($ast);

// Children can be iterated directly
foreach ($cursor as $child) {
    if ($child->node instanceof ExpressionNode) {
        Example::dump($child->node);
        break;
    }
}

// Also possible to get n-th child
Example::dump($cursor[2]);

// And next/previous
Example::dump($cursor[2]->next->node ?? null);
Example::dump($cursor[2]->previous->node ?? null);
