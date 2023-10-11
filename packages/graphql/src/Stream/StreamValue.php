<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\Stream;

class StreamValue {
    /**
     * @param Stream<object> $stream
     */
    public function __construct(
        public readonly Stream $stream,
    ) {
        // empty
    }

    public function __isset(string $name): bool {
        return match ($name) {
            'items'     => true,
            'offset'    => true,
            'length'    => true,
            'navigator' => true,
            'previous'  => true,
            'current'   => true,
            'next'      => true,
            default     => false,
        };
    }

    public function __get(string $name): mixed {
        return match ($name) {
            'items'     => $this->stream->getItems(),
            'offset'    => $this->stream->getOffset(),
            'length'    => $this->stream->getLength(),
            'navigator' => $this,
            'previous'  => $this->stream->getPreviousCursor(),
            'current'   => $this->stream->getCurrentCursor(),
            'next'      => $this->stream->getNextCursor(),
            default     => null,
        };
    }
}
