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
use LastDragon_ru\LaraASP\GraphQL\AstManipulator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Unsortable;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\FailedToCreateSortClause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\FailedToCreateSortClauseForField;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

use function count;
use function str_starts_with;

class Manipulator extends AstManipulator {
    // <editor-fold desc="API">
    // =========================================================================
    public function update(InputValueDefinitionNode $node, FieldDefinitionNode $query): void {
        // Convert
        $type          = null;
        $isPlaceholder = $this->isPlaceholder($node);

        if ($isPlaceholder || !$this->isTypeName($node)) {
            $definition  = $this->getTypeDefinitionNode($isPlaceholder ? $query : $node);
            $isSupported = $definition instanceof InputObjectTypeDefinitionNode
                || $definition instanceof ObjectTypeDefinitionNode
                || $definition instanceof InputObjectType
                || $definition instanceof ObjectType;

            if ($isSupported) {
                $name = $this->getInputType($definition);

                if ($name) {
                    $type = Parser::typeReference("[{$name}!]");
                }
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

    // <editor-fold desc="Types">
    // =========================================================================
    protected function getInputType(
        InputObjectTypeDefinitionNode|ObjectTypeDefinitionNode|InputObjectType|ObjectType $node,
    ): ?string {
        // Exists?
        $name = $this->getTypeName($node);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Add type
        $type = $this->addTypeDefinition(Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Sort clause for {$this->getNodeTypeFullName($node)} (only one property allowed at a time).
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
            if (
                !($field instanceof InputObjectField || $field instanceof InputValueDefinitionNode)
                && $this->getNodeDirective($field, FieldResolver::class)
            ) {
                continue;
            }

            // Unsortable?
            if ($this->getNodeDirective($field, Unsortable::class)) {
                continue;
            }

            // Is supported?
            $fieldDefinition = Directive::TypeDirection;
            $fieldTypeNode   = $this->getTypeDefinitionNode($field);
            $isNested        = $fieldTypeNode instanceof InputObjectTypeDefinitionNode
                || $fieldTypeNode instanceof ObjectTypeDefinitionNode
                || $fieldTypeNode instanceof InputObjectType
                || $fieldTypeNode instanceof ObjectType;

            if ($isNested) {
                $fieldDefinition = $this->getInputType($fieldTypeNode);
            } else {
                // empty
            }

            // Create new Field
            if (!$fieldDefinition || !$this->copyFieldToType($type, $field, $fieldDefinition, $description)) {
                throw new FailedToCreateSortClauseForField($this->getNodeName($node), $this->getNodeName($field));
            }
        }

        // Remove dummy
        unset($type->fields[0]);

        // Empty?
        if (count($type->fields) === 0) {
            $this->removeTypeDefinition($name);

            $name = null;
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
    protected function isTypeName(
        Node|Type|InputObjectField|FieldDefinition|string $node,
    ): bool {
        return str_starts_with($this->getNodeTypeName($node), Directive::Name);
    }

    protected function getTypeName(
        InputObjectTypeDefinitionNode|ObjectTypeDefinitionNode|InputObjectType|ObjectType $node,
    ): string {
        return Directive::Name."Clause{$this->getNodeName($node)}";
    }
    // </editor-fold>
}
