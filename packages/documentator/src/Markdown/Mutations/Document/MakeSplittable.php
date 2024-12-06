<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Composite;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Footnote\Remove as FootnotesRemove;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Link\RemoveToSelf;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference\Inline as ReferencesInline;

/**
 * Inlines all references, removes footnotes, etc, to make possible
 * extract any block/paragraph from the document without losing
 * information.
 */
readonly class MakeSplittable extends Composite {
    public function __construct() {
        parent::__construct(
            new FootnotesRemove(),
            new ReferencesInline(),
            new RemoveToSelf(),
        );
    }
}
