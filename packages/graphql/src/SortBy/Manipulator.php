<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use LastDragon_ru\LaraASP\GraphQL\AstManipulator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\FailedCreateSortClause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\FailedCreateSortClauseForField;

class Manipulator extends AstManipulator {
    // <editor-fold desc="API">
    // =========================================================================
    public function update(InputValueDefinitionNode $node): ListTypeNode {
        // Convert
        $type = null;

        if (!($node->type instanceof ListTypeNode)) {
            $def = $this->getTypeDefinitionNode($node);

            if ($def instanceof InputObjectTypeDefinitionNode || $def instanceof InputObjectType) {
                $name = $this->getInputType($def);
                $type = Parser::typeReference("[{$name}!]");
            }
        } else {
            $type = $node->type;
        }

        if (!($type instanceof ListTypeNode)) {
            throw new FailedCreateSortClause($this->getNodeTypeName($node));
        }

        // Update
        $node->type = $type;

        // Return
        return $type;
    }
    // </editor-fold>

    // <editor-fold desc="Types">
    // =========================================================================
    protected function getInputType(InputObjectTypeDefinitionNode|InputObjectType $node): string {
        // Exists?
        $name = $this->getTypeName($node);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Add type
        $type = $this->addTypeDefinition(Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Sort clause for input {$this->getNodeName($node)} (only one property allowed at a time).
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
        $fields      = $node instanceof InputObjectType
            ? $node->getFields()
            : $node->fields;

        foreach ($fields as $field) {
            /** @var InputValueDefinitionNode|InputObjectField $field */

            // Is supported?
            $fieldDefinition = Directive::TypeDirection;
            $fieldTypeNode   = $field instanceof InputValueDefinitionNode
                ? $this->getTypeDefinitionNode($field)
                : $field->getType();

            if ($fieldTypeNode instanceof InputObjectTypeDefinitionNode || $fieldTypeNode instanceof InputObjectType) {
                $fieldDefinition = $this->getInputType($fieldTypeNode);
            } else {
                // empty
            }

            // Create new Field
            if ($field instanceof InputValueDefinitionNode) {
                // TODO [SortBy] We probably not need all directives from the
                //      original Input type, but cloning is the easiest way...
                $clone = $field->cloneDeep();

                if ($clone instanceof InputValueDefinitionNode) {
                    $clone->type        = $fieldDefinition;
                    $clone->description = $description;
                    $type->fields[]     = $clone;
                } else {
                    throw new FailedCreateSortClauseForField($node->name->value, $field->name->value);
                }
            } else {
                $type->fields[] = Parser::inputValueDefinition(
                    <<<DEF
                    """
                    {$description}
                    """
                    {$field->name}: {$fieldDefinition}
                    DEF,
                );
            }
        }

        // Remove dummy
        unset($type->fields[0]);

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
    protected function getTypeName(InputObjectTypeDefinitionNode|InputObjectType $node): string {
        return Directive::Name."Clause{$this->getNodeName($node)}";
    }
    // </editor-fold>
}
