<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

class ObjectBlockList extends BlockList {
    protected function getPrefix(): string {
        return '{';
    }

    protected function getSuffix(): string {
        return '}';
    }

    protected function isAlwaysMultiline(): bool {
        return true;
    }
}
