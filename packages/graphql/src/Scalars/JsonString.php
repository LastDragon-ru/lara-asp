<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Scalars;

class JsonString implements JsonStringable {
    public function __construct(
        private readonly string $json,
    ) {
        // empty
    }

    public function __toString(): string {
        return $this->json;
    }
}
