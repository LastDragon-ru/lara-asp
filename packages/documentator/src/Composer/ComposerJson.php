<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Composer;

use LastDragon_ru\LaraASP\Serializer\Contracts\Partial;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

/**
 * @see https://getcomposer.org/doc/04-schema.md
 */
readonly class ComposerJson implements Serializable, Partial {
    public function __construct(
        public ?string $name = null,
        public ?string $readme = 'README.md',
        /**
         * @var array<string, string>
         */
        public array $require = [],
    ) {
        // empty
    }
}
