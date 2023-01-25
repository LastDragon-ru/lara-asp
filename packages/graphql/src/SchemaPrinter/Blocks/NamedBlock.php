<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks;

/**
 * @internal
 */
interface NamedBlock {
    public function getName(): string;
}
