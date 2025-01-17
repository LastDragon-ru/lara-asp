<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Documentator\Editor\Locations\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\Contracts\Mutation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Document;
use Override;

readonly class Body implements Mutation {
    public function __construct() {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function __invoke(Document $document): iterable {
        $endLine  = (SummaryData::get($document->node) ?? TitleData::get($document->node))?->getEndLine();
        $location = $endLine !== null
            ? [[new Location(0, $endLine), null]]
            : [];

        return $location;
    }
}
