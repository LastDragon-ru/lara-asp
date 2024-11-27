<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Template;

readonly class Data {
    public function __construct(
        /**
         * @var non-empty-list<Document>
         */
        public array $documents,
        /**
         * @var int<1, 6>
         */
        public int $level,
    ) {
        // empty
    }
}
