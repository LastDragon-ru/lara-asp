<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream;

use Nuwave\Lighthouse\Execution\ResolveInfo;

class Stream {
    /**
     * @param int<1, max> $chunk
     */
    public function __construct(
        protected readonly ResolveInfo $info,
        protected readonly object $builder,
        protected readonly string $key,
        protected readonly Cursor $cursor,
        protected readonly int $chunk,
    ) {
        // empty
    }
}
