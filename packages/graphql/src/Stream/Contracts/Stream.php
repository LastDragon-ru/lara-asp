<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Contracts;

use LastDragon_ru\LaraASP\GraphQL\Stream\Offset;

interface Stream {
    /**
     * @return iterable<array-key, mixed>
     */
    public function getItems(): iterable;

    /**
     * @return int<0, max>|null
     */
    public function getLength(): ?int;

    public function getNextOffset(): ?Offset;

    public function getCurrentOffset(): Offset;

    public function getPreviousOffset(): ?Offset;
}
