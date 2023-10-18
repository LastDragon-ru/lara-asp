<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use LastDragon_ru\LaraASP\GraphQL\Stream\Contracts\Stream;

class StreamValue {
    public function __construct(
        public readonly Stream $stream,
    ) {
        // empty
    }

    public function __isset(string $name): bool {
        return match ($name) {
            'items'      => true,
            'length'     => true,
            'navigation' => true,
            'previous'   => true,
            'current'    => true,
            'next'       => true,
            default      => false,
        };
    }

    public function __get(string $name): mixed {
        return match ($name) {
            'items'      => $this->stream->getItems(),
            'length'     => $this->stream->getLength(),
            'navigation' => $this,
            'previous'   => $this->stream->getPreviousCursor(),
            'current'    => $this->stream->getCurrentCursor(),
            'next'       => $this->stream->getNextCursor(),
            default      => null,
        };
    }
}
