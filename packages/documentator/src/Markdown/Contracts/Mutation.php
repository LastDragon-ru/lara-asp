<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Contracts;

use LastDragon_ru\LaraASP\Documentator\Editor\Coordinate;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;

interface Mutation {
    /**
     * @return iterable<mixed, array{iterable<mixed, Coordinate>, ?string}>
     */
    public function __invoke(Document $document): iterable;
}
