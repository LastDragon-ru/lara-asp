<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator as BuilderManipulator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\FailedToCreateSortClause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Clause;

use function str_starts_with;

class Manipulator extends BuilderManipulator {
    // <editor-fold desc="API">
    // =========================================================================
    public function update(InputValueDefinitionNode $node, FieldDefinitionNode $query): void {
        // Convert
        $type          = null;
        $isPlaceholder = $this->isPlaceholder($node);

        if ($isPlaceholder || !$this->isTypeName($node)) {
            $definition  = $isPlaceholder
                ? $this->getPlaceholderTypeDefinitionNode($query)
                : $this->getTypeDefinitionNode($node);
            $isSupported = $definition instanceof InputObjectTypeDefinitionNode
                || $definition instanceof ObjectTypeDefinitionNode
                || $definition instanceof InputObjectType
                || $definition instanceof ObjectType;

            if ($isSupported) {
                $name = $this->getType(Clause::class, $this->getNodeTypeName($definition), $this->isNullable($node));
                $type = Parser::typeReference("[{$name}!]");
            }
        } else {
            $type = $node->type;
        }

        // Success?
        if (!$type) {
            throw new FailedToCreateSortClause(
                $this->getNodeTypeFullName($isPlaceholder ? $query : $node),
            );
        }

        // Update
        $node->type = $type;
    }
    // </editor-fold>

    // <editor-fold desc="Names">
    // =========================================================================
    protected function isTypeName(
        Node|Type|InputObjectField|FieldDefinition|string $node,
    ): bool {
        return str_starts_with($this->getNodeTypeName($node), Directive::Name);
    }
    // </editor-fold>
}
