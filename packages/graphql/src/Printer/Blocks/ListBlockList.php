<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Printer\Blocks;

class ListBlockList extends BlockList {
    protected function getPrefix(): string {
        return '[';
    }

    protected function getSuffix(): string {
        return ']';
    }
}
