<?php declare(strict_types = 1);

namespace LastDragon_ru\DiyParser\Ast;

use ArrayAccess;
use Countable;
use IteratorAggregate;

/**
 * @template TChild of object
 *
 * @extends IteratorAggregate<int, TChild>
 * @extends ArrayAccess<int<0, max>, ?TChild>
 */
interface ParentNode extends IteratorAggregate, ArrayAccess, Countable {
    // empty
}
