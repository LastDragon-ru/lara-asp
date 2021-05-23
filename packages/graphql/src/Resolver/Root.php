<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Resolver;

class Root {
    public function __construct(
        protected mixed $root,
    ) {
        // empty
    }

    public function get(): mixed {
        return $this->root;
    }
}
