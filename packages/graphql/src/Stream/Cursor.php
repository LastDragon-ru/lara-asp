<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class Cursor implements Serializable {
    /**
     * @param non-empty-array<string, scalar|null>|null $cursor
     * @param int<0, max>|null                          $offset
     */
    public function __construct(
        public string $path,
        public array|null $cursor = null,
        public int|null $offset = null,
    ) {
        // empty
    }
}
