<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Template;

use LastDragon_ru\Path\FilePath;

readonly class Document {
    public function __construct(
        public FilePath $path,
        public string $title,
        public string $summary,
    ) {
        // empty
    }
}
