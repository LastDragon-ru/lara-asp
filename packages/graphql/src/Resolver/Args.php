<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Resolver;

use Illuminate\Contracts\Support\Arrayable;

class Args implements Arrayable {
    /**
     * @param array<string,mixed> $args
     */
    public function __construct(
        protected array $args,
    ) {
        // empty
    }

    /**
     * @return array<string,mixed>
     */
    public function get(): array {
        return $this->args;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array {
        return $this->get();
    }
}
