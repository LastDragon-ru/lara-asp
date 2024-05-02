<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Stream\Types;

use GraphQL\Language\AST\TypeDefinitionNode;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\Context;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeDefinition;
use LastDragon_ru\LaraASP\GraphQL\Builder\Contracts\TypeSource;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator;
use LastDragon_ru\LaraASP\GraphQL\Stream\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\Stream\Scalars\Offset as OffsetScalar;
use Override;

class Offset implements TypeDefinition {
    #[Override]
    public function getTypeName(TypeSource $source, Context $context): string {
        return Directive::Name.'Offset';
    }

    #[Override]
    public function getTypeDefinition(
        Manipulator $manipulator,
        TypeSource $source,
        Context $context,
        string $name,
    ): TypeDefinitionNode|string|null {
        return OffsetScalar::class;
    }
    // </editor-fold>
}
