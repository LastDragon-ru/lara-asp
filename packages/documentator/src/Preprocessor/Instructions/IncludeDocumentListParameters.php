<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Preprocessor\Instructions;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use Symfony\Component\Finder\Finder;

class IncludeDocumentListParameters implements Serializable {
    public function __construct(
        /**
         * @see Finder::depth()
         * @var array<array-key, string|int>|string|int|null
         */
        public readonly array|string|int|null $depth = 0,
        public readonly ?string $template = null,
    ) {
        // empty
    }
}
