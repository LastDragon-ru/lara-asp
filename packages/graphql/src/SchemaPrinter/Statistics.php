<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SchemaPrinter;

interface Statistics {
    /**
     * @return array<string,string>
     */
    public function getUsedTypes(): array;

    /**
     * @return array<string,string>
     */
    public function getUsedDirectives(): array;
}
