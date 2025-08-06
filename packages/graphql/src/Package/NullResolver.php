<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Package;

/**
 * @internal
 */
class NullResolver {
    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(mixed $root, array $args): mixed {
        return null;
    }
}
