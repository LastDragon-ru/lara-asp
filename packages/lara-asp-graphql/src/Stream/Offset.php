<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class Offset implements Serializable {
    /**
     * @param non-empty-array<string, scalar|null>|null $cursor
     * @param int<0, max>|null                          $offset
     */
    public function __construct(
        public string $path,
        public ?int $offset = null,
        public ?array $cursor = null,
    ) {
        // empty
    }
}
