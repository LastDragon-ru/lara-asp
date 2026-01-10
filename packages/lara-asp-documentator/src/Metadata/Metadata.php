<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Metadata;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

/**
 * @internal
 */
class Metadata implements Serializable {
    public function __construct(
        /**
         * Package version.
         */
        public ?string $version = null,
        /**
         * Requirements to show.
         *
         * @var array<string, string>
         */
        public array $require = [],
        /**
         * Defines how to merge packages (`illuminate/*`, `symfony/*`, etc)
         *
         * @var array<string, string>|null
         */
        public ?array $merge = null,
        /**
         * Cached requirements.
         *
         * @var array<string, array<string, list<string>>>
         */
        public array $requirements = [],
    ) {
        // empty
    }
}
