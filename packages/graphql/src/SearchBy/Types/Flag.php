<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\PhpEnumType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Enums\Flag as FlagEnum;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Directives\Directive;
use Override;

class Flag implements TypeDefinition {
    public function __construct() {
        // empty
    }

    #[Override]
    public function getTypeName(Manipulator $manipulator, TypeSource $source): string {
        return Directive::Name.'TypeFlag';
    }

    #[Override]
    public function getTypeDefinition(
        Manipulator $manipulator,
        TypeSource $source,
        string $name,
    ): TypeDefinitionNode|Type|null {
        return new PhpEnumType(FlagEnum::class, $name);
    }
}
