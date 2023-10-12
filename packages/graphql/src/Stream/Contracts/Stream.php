<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Stream\Cursor;

/**
 * @template TBuilder of object
 */
interface Stream {
    /**
     * @return iterable<array-key, mixed>
     */
    public function getItems(): iterable;

    /**
     * @return int<0, max>|null
     */
    public function getLength(): ?int;

    public function getNextCursor(): ?Cursor;

    public function getCurrentCursor(): Cursor;

    public function getPreviousCursor(): ?Cursor;
}
