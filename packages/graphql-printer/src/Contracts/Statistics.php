<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Contracts;

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
