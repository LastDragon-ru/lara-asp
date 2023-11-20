<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Scalars;

use Override;

class JsonString implements JsonStringable {
    public function __construct(
        private readonly string $json,
    ) {
        // empty
    }

    #[Override]
    public function __toString(): string {
        return $this->json;
    }
}
