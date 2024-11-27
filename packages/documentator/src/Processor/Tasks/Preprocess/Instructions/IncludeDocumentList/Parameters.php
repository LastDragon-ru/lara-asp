<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList;

use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Contracts\Parameters as ParametersContract;
use LastDragon_ru\LaraASP\Documentator\Processor\Tasks\Preprocess\Instructions\IncludeDocumentList\Template\Data;
use LastDragon_ru\LaraASP\Documentator\Utils\SortOrder;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Symfony\Component\Finder\Finder;

class Parameters implements ParametersContract, Serializable {
    public function __construct(
        /**
         * Directory path.
         */
        public readonly string $target,
        /**
         * [Directory Depth](https://symfony.com/doc/current/components/finder.html#directory-depth)
         * (eg the `0` means no nested directories, the `null` removes limits).
         *
         * @see Finder::depth()
         * @var array<array-key, string|int>|string|int|null
         */
        public readonly array|string|int|null $depth = 0,
        /**
         * Blade template. The documents passed in the `$data` ({@see Data})
         * variable. Also, be careful with leading whitespaces.
         *
         * @see Data
         */
        public readonly string $template = 'default',
        /**
         * Sort order.
         */
        public readonly SortOrder $order = SortOrder::Asc,
        /**
         * Headings level. Possible values are
         *
         * * `null`: `<current level> + 1`
         * * `int`: explicit level (`1-6`)
         * * `0`: `<current level>`
         */
        public readonly ?int $level = null,
    ) {
        // empty
    }
}
