<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Scalars;

use Override;

readonly class JsonString implements JsonStringable {
    public function __construct(
        private string $json,
    ) {
        // empty
    }

    #[Override]
    public function __toString(): string {
        return $this->json;
    }
}
