<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter\Blocks;

/**
 * @internal
 */
interface Named {
    public function getName(): string;
}
