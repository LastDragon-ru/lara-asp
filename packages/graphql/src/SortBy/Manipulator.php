<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use LastDragon_ru\LaraASP\GraphQL\AstManipulator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\FailedToCreateSortClause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\FailedToCreateSortClauseForField;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

use function count;

class Manipulator extends AstManipulator {
    // <editor-fold desc="API">
    // =========================================================================
    public function update(InputValueDefinitionNode $node, FieldDefinitionNode $query): ListTypeNode {
        // Convert
        $type = null;

        if (!($node->type instanceof ListTypeNode)) {
            $definition  = $this->getTypeDefinitionNode($this->isPlaceholder($node) ? $query : $node);
            $isSupported = $definition instanceof InputObjectTypeDefinitionNode
                || $definition instanceof ObjectTypeDefinitionNode
                || $definition instanceof InputObjectType
                || $definition instanceof ObjectType;

            if ($isSupported) {
                $name = $this->getInputType($definition);
                $type = Parser::typeReference("[{$name}!]");
            }
        } else {
            $type = $node->type;
        }

        if (!($type instanceof ListTypeNode)) {
            throw new FailedToCreateSortClause($this->getNodeTypeName($node));
        }

        // Update
        $node->type = $type;

        // Return
        return $type;
    }
    // </editor-fold>

    // <editor-fold desc="Types">
    // =========================================================================
    protected function getInputType(
        InputObjectTypeDefinitionNode|ObjectTypeDefinitionNode|InputObjectType|ObjectType $node,
    ): string {
        // Exists?
        $name = $this->getTypeName($node);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Add type
        $type = $this->addTypeDefinition(Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Sort clause for {$this->getNodeFullName($node)} (only one property allowed at a time).
            """
            input {$name} {
                """
                If you see this probably something wrong. Please contact to developer.
                """
                dummy: ID
            }
            DEF,
        ));

        // Add sortable fields
        $description = 'Property clause.';
        $fields      = $node instanceof InputObjectType || $node instanceof ObjectType
            ? $node->getFields()
            : $node->fields;

        foreach ($fields as $field) {
            // Convertable?
            if ($this->isList($field)) {
                continue;
            }

            // Resolver?
            if ($this->getNodeDirective($field, FieldResolver::class)) {
                continue;
            }

            // Ignored?
            // TODO Not implemented

            // Is supported?
            $fieldDefinition = Directive::TypeDirection;
            $fieldTypeNode   = $this->getTypeDefinitionNode($field);

            if ($fieldTypeNode instanceof InputObjectTypeDefinitionNode || $fieldTypeNode instanceof InputObjectType) {
                $fieldDefinition = $this->getInputType($fieldTypeNode);
            } else {
                // empty
            }

            // Create new Field
            if (!$this->copyFieldToType($type, $field, $fieldDefinition, $description)) {
                throw new FailedToCreateSortClauseForField($this->getNodeName($node), $this->getNodeName($field));
            }
        }

        // Remove dummy
        unset($type->fields[0]);

        // Empty?
        if (count($type->fields) === 0) {
            throw new FailedToCreateSortClause($this->getNodeFullName($node));
        }

        // Return
        return $name;
    }
    // </editor-fold>

    // <editor-fold desc="Defaults">
    // =========================================================================
    protected function addDefaultTypeDefinitions(): void {
        $name = Directive::TypeDirection;

        if (!$this->isTypeDefinitionExists($name)) {
            $this->addTypeDefinition(Parser::enumTypeDefinition(
                /** @lang GraphQL */
                <<<GRAPHQL
                """
                Sort direction.
                """
                enum {$name} {
                    asc
                    desc
                }
                GRAPHQL,
            ));
        }
    }
    // </editor-fold>

    // <editor-fold desc="Names">
    // =========================================================================
    protected function getTypeName(
        InputObjectTypeDefinitionNode|ObjectTypeDefinitionNode|InputObjectType|ObjectType $node,
    ): string {
        return Directive::Name."Clause{$this->getNodeName($node)}";
    }
    // </editor-fold>
}
