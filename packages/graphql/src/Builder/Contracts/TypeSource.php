<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use Stringable;

interface TypeSource extends Stringable {
    /**
     * @return (TypeDefinitionNode&Node)|(TypeNode&Node)|Type
     */
    public function getType(): TypeDefinitionNode|TypeNode|Type;

    public function getTypeName(): string;

    /**
     * @return (TypeDefinitionNode&Node)|Type
     */
    public function getTypeDefinition(): TypeDefinitionNode|Type;

    public function isNullable(): bool;

    public function isList(): bool;

    public function isUnion(): bool;

    public function isObject(): bool;
}
