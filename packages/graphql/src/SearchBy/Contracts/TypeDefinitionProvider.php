<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts;

interface TypeDefinitionProvider {
    /**
     * @return array<string,class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Contracts\TypeDefinition>>
     */
    public function getDefinitions(): array;
}
