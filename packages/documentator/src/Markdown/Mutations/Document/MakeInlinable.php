<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Document;

use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Composite;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Footnote\Prefix as FootnotesPrefix;
use LastDragon_ru\LaraASP\Documentator\Markdown\Mutations\Reference\Prefix as ReferencesPrefix;

/**
 * Renames all references/footnotes/etc to make possible inline the
 * document into another document without conflicts/ambiguities.
 */
readonly class MakeInlinable extends Composite {
    public function __construct(string $prefix) {
        parent::__construct(
            new FootnotesPrefix($prefix),
            new ReferencesPrefix($prefix),
        );
    }
}
