<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Contracts;

use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;

interface Mutation {
    /**
     * @return iterable<array-key, array{Location, ?string}>
     */
    public function __invoke(Document $document): iterable;
}
