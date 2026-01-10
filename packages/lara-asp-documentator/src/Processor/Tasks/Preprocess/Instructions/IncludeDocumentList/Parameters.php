<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Template\Data;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;

readonly class Parameters implements InstructionParameters {
    public function __construct(
        /**
         * Directory path.
         *
         * @var non-empty-string
         */
        public string $target,
        /**
         * Blade template. The documents passed in the `$data` ({@see Data})
         * variable. Also, be careful with leading whitespaces.
         *
         * @see Data
         */
        public string $template = 'default',
        /**
         * Sort order.
         */
        public SortOrder $order = SortOrder::Asc,
        /**
         * Headings level. Possible values are
         *
         * * `null`: `<current level> + 1`
         * * `int`: explicit level (`1-6`)
         * * `0`: `<current level>`
         */
        public ?int $level = null,
        /**
         * Glob(s) to include (only Markdown documents expected).
         *
         * @var list<string>
         */
        public array $include = ['*.md'],
        /**
         * Glob(s) to exclude.
         *
         * @var list<string>
         */
        public array $exclude = [],
    ) {
        // empty
    }
}
