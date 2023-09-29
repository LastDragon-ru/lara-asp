<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class Cursor implements Serializable {
    /**
     * @param int<1, max>                               $chunk
     * @param non-empty-array<string, scalar|null>|null $cursor
     * @param int<0, max>|null                          $offset
     * @param array<string, mixed>|null                 $where
     * @param array<string, mixed>|null                 $order
     */
    public function __construct(
        public string $key,
        public int $chunk,
        public array|null $cursor = null,
        public int|null $offset = null,
        public ?array $where = null,
        public ?array $order = null,
    ) {
        // empty
    }
}
