<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SortBy;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ListTypeNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\Parser;
use LastDragon_ru\LaraASP\GraphQL\AstManipulator as BaseAstManipulator;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;

use function is_null;
use function sprintf;
use function tap;

class AstManipulator extends BaseAstManipulator {
    public function __construct(
        protected DocumentAST $document,
        protected string $name,
    ) {
        parent::__construct($this->document);
    }

    // <editor-fold desc="API">
    // =========================================================================
    public function getType(InputValueDefinitionNode $node): ListTypeNode {
        $type = null;

        if (!($node->type instanceof ListTypeNode)) {
            $def = $this->getTypeDefinitionNode($node);

            if ($def instanceof InputObjectTypeDefinitionNode) {
                $name = $this->getInputType($def);
                $type = Parser::typeReference("[{$name}!]");
            }
        } else {
            $type = $node->type;
        }

        if (!($type instanceof ListTypeNode)) {
            throw new SortByException(sprintf(
                'Impossible to create Sort Clause for `%s`.',
                $node->name->value,
            ));
        }

        return $type;
    }
    // </editor-fold>

    // <editor-fold desc="Types">
    // =========================================================================
    protected function getInputType(InputObjectTypeDefinitionNode $node): string {
        // Exists?
        $name = $this->getTypeName($node);

        if ($this->isTypeDefinitionExists($name)) {
            return $name;
        }

        // Add type
        $type = $this->addTypeDefinition($name, Parser::inputObjectTypeDefinition(
            <<<DEF
            """
            Sort clauses.
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
        $reference   = Parser::typeReference($this->getMap($this)[SortByDirective::TypeDirection]);
        $description = Parser::description('"""Property clause."""');

        /** @var \GraphQL\Language\AST\InputValueDefinitionNode $field */
        foreach ($node->fields as $field) {
            // Is supported?
            $fieldType     = ASTHelper::getUnderlyingTypeName($field);
            $fieldTypeNode = $this->getTypeDefinitionNode($field);

            if (is_null($fieldTypeNode)) {
                $fieldTypeNode = $this->getScalarTypeNode($fieldType);
            }

            if (!($fieldTypeNode instanceof ScalarTypeDefinitionNode)) {
                continue;
            }

            // Create new Field
            // TODO [SortBy] We probably not need all directives from the
            //      original Input type, but cloning is the easiest way...
            $type->fields[] = tap(
                $field->cloneDeep(),
                static function (InputValueDefinitionNode $field) use ($reference, $description): void {
                    $field->type        = $reference;
                    $field->description = $description;
                },
            );
        }

        // Remove dummy
        unset($type->fields[0]);

        // Return
        return $name;
    }
    // </editor-fold>

    // <editor-fold desc="Defaults">
    // =========================================================================
    protected function addRootTypeDefinitions(): void {
        $type = SortByDirective::TypeDirection;

        $this->addTypeDefinitions($this, [
            $type => Parser::enumTypeDefinition(
            /** @lang GraphQL */
                <<<GRAPHQL
                enum {$this->name}{$type} {
                    asc
                    desc
                }
                GRAPHQL,
            ),
        ]);
    }
    // </editor-fold>

    // <editor-fold desc="Names">
    // =========================================================================
    protected function getTypeName(InputObjectTypeDefinitionNode $node): string {
        return "{$this->name}Clause{$node->name->value}";
    }
    // </editor-fold>
}
