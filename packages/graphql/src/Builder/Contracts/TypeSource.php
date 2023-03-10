<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Type\Definition\Type;
use Stringable;

interface TypeSource extends Stringable {
    public function getType(): TypeDefinitionNode|NamedTypeNode|ListTypeNode|NonNullTypeNode|Type;

    public function getTypeName(): string;

    public function getTypeDefinition(): TypeDefinitionNode|Type;

    public function isNullable(): bool;

    public function isList(): bool;
}
