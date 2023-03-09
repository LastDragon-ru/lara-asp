<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Contracts;

use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\TypeNode;
use GraphQL\Type\Definition\Type;
use Stringable;

interface TypeSource extends Stringable {
    /**
     * @return TypeDefinitionNode|(Node&TypeNode)|Type
     */
    public function getType(): TypeDefinitionNode|Node|Type;

    public function getTypeName(): string;

    public function isNullable(): ?bool;

    public function isList(): ?bool;
}
