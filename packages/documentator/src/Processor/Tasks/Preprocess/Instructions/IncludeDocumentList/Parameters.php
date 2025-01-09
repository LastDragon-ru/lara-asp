<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as InstructionParameters;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Template\Data;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use Symfony\Component\Finder\Finder;

readonly class Parameters implements InstructionParameters {
    public function __construct(
        /**
         * Directory path.
         */
        public string $target,
        /**
         * [Directory Depth](https://symfony.com/doc/current/components/finder.html#directory-depth)
         * (eg the `0` means no nested directories, the `null` removes limits).
         *
         * @see Finder::depth()
         *
         * @var array<array-key, string|int>|string|int|null
         */
        public array|string|int|null $depth = 0,
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
         * [Rules which filenames must match](https://symfony.com/doc/current/components/finder.html#path)
         * (only Markdown documents will be listed).
         *
         * @see Finder::path()
         *
         * @var array<array-key, string>|string|null
         */
        public array|string|null $include = null,
    ) {
        // empty
    }
}
