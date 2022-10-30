<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\InputObjectField;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\Builder\Manipulator as BuilderManipulator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Contracts\Ignored;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Directives\Directive;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Exceptions\FailedToCreateSortClause;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\Property;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Operators\PropertyOperator;
use LastDragon_ru\LaraASP\GraphQL\SortBy\Types\Direction;
use Nuwave\Lighthouse\Pagination\PaginateDirective;
use Nuwave\Lighthouse\Pagination\PaginationType;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

use function count;
use function mb_strlen;
use function mb_substr;
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
        $name = $this->getInputTypeName($node);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Add type
        $type = $this->addTypeDefinition(
            Parser::inputObjectTypeDefinition(
                <<<DEF
                """
                Sort clause for `{$this->getNodeTypeFullName($node)}` (only one property allowed at a time).
                """
                input {$name} {
                    """
                    If you see this probably something wrong. Please contact to developer.
                    """
                    dummy: ID
                }
                DEF,
            ),
        );

        // Add sortable fields
        $direction = $this->getType(Direction::class);
        $operator  = $this->getContainer()->make(PropertyOperator::class);
        $property  = $this->getContainer()->make(Property::class);
        $builder   = $this->getBuilderInfo()->getBuilder();
        $fields    = $node instanceof InputObjectType || $node instanceof ObjectType
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

            // Ignored?
            if ($this->getNodeDirective($field, Ignored::class)) {
                continue;
            }

            // Is supported?
            $fieldType     = $direction;
            $fieldOperator = $operator;
            $fieldTypeNode = $this->getTypeDefinitionNode($field);
            $isNested      = $fieldTypeNode instanceof InputObjectTypeDefinitionNode
                || $fieldTypeNode instanceof ObjectTypeDefinitionNode
                || $fieldTypeNode instanceof InputObjectType
                || $fieldTypeNode instanceof ObjectType;

            if ($isNested) {
                if ($property->isBuilderSupported($builder)) {
                    $fieldType     = $this->getInputType($fieldTypeNode);
                    $fieldOperator = $property;
                } else {
                    $fieldType     = null;
                    $fieldOperator = null;
                }
            } else {
                // empty
            }

            // Create new Field
            if ($fieldType && $fieldOperator) {
                $type->fields[] = Parser::inputValueDefinition(
                    $this->getOperatorField(
                        $fieldOperator,
                        $this->getTypeDefinitionNode($fieldType),
                        $this->getNodeName($field),
                    ),
                );
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

    // <editor-fold desc="Names">
    // =========================================================================
    protected function isTypeName(
        Node|Type|InputObjectField|FieldDefinition|string $node,
    ): bool {
        return str_starts_with($this->getNodeTypeName($node), Directive::Name);
    }

    protected function getTypeName(string $name, string $type = null, bool $nullable = null): string {
        return Directive::Name.'Type'.Str::studly($name);
    }

    protected function getInputTypeName(
        InputObjectTypeDefinitionNode|ObjectTypeDefinitionNode|InputObjectType|ObjectType $node,
    ): string {
        $directiveName = Directive::Name;
        $builderName   = $this->getBuilderInfo()->getName();
        $nodeName      = $this->getNodeName($node);

        return "{$directiveName}{$builderName}Clause{$nodeName}";
    }
    // </editor-fold>

    // <editor-fold desc="AST Helpers">
    // =========================================================================
    protected function getPlaceholderTypeDefinitionNode(FieldDefinitionNode $field): TypeDefinitionNode|Type|null {
        $node     = null;
        $paginate = $this->getNodeDirective($field, PaginateDirective::class);

        if ($paginate) {
            $type       = $this->getNodeTypeName($this->getTypeDefinitionNode($field));
            $pagination = (new class() extends PaginateDirective {
                public function getPaginationType(PaginateDirective $directive): PaginationType {
                    return $directive->paginationType();
                }
            })->getPaginationType($paginate);

            if ($pagination->isPaginator()) {
                $type = mb_substr($type, 0, -mb_strlen('Paginator'));
            } elseif ($pagination->isSimple()) {
                $type = mb_substr($type, 0, -mb_strlen('SimplePaginator'));
            } elseif ($pagination->isConnection()) {
                $type = mb_substr($type, 0, -mb_strlen('Connection'));
            } else {
                // empty
            }

            if ($type) {
                $node = $this->getTypeDefinitionNode($type);
            }
        } else {
            $node = $this->getTypeDefinitionNode($field);
        }

        return $node;
    }
    // </editor-fold>
}
