<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

class Stream {
    public function __construct(
        protected readonly object $builder,
        protected readonly Cursor $cursor,
    ) {
        // empty
    }
}
