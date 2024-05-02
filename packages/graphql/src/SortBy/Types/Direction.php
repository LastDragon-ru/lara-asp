<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Enums\Direction as DirectionEnum;
use LastDragon_ru\LaraASP\GraphQL\Utils\TypeReference;
use Override;

class Direction implements TypeDefinition {
    public function __construct() {
        // empty
    }

    #[Override]
    public function getTypeName(TypeSource $source, Context $context): string {
        return Directive::Name.'TypeDirection';
    }

    #[Override]
    public function getTypeDefinition(
        Manipulator $manipulator,
        TypeSource $source,
        Context $context,
        string $name,
    ): TypeDefinitionNode|TypeReference|null {
        return new TypeReference($name, DirectionEnum::class);
    }
}
