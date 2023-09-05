<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;

class Cursor implements Serializable {
    /**
     * @param int<0, max>|null          $offset
     * @param array<string, mixed>|null $where
     * @param array<string, mixed>|null $order
     */
    public function __construct(
        public string|int|null $key = null,
        public ?int $offset = null,
        public ?int $chunk = null,
        public ?array $where = null,
        public ?array $order = null,
    ) {
        // empty
    }
}
