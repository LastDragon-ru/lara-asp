<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\SearchBy;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Language\Parser;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Str;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Between;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\Equal;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\GreaterThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\GreaterThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\In;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\IsNotNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\IsNull;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\LessThan;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\LessThanOrEqual;
use LastDragon_ru\LaraASP\GraphQL\SearchBy\Operators\NotEqual;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgManipulator;

use function array_merge;
use function implode;
use function is_array;
use function is_null;
use function sprintf;
use function str_ends_with;

class SearchByDirective extends BaseDirective implements ArgManipulator {
    protected const NAME = 'SearchBy';

    /**
     * @var array<string, \LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator>|null
     */
    protected ?array $operators = [];

    protected Container $container;

    /**
     * Determines operators available for each scalar type.
     *
     * @var array<string, array<string>|string>
     */
    protected array $scalars = [
        'ID'      => [
            Equal::class,
            NotEqual::class,
            LessThan::class,
            LessThanOrEqual::class,
            GreaterThan::class,
            GreaterThanOrEqual::class,
            In::class,
            Between::class,
        ],
        'Int'     => [
            Equal::class,
            NotEqual::class,
            LessThan::class,
            LessThanOrEqual::class,
            GreaterThan::class,
            GreaterThanOrEqual::class,
            In::class,
            Between::class,
        ],
        'Float'   => 'Int',
        'Boolean' => [
            Equal::class,
            NotEqual::class,
        ],
        'String'  => [
            Equal::class,
            NotEqual::class,
            In::class,
        ],
    ];

    /**
     * @param array<string, array<class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator>>> $scalars
     */
    public function __construct(Container $container, array $scalars) {
        $this->container = $container;
        $this->scalars   = array_merge($this->scalars, $scalars);
    }

    public static function definition(): string {
        return /** @lang GraphQL */ <<<'GRAPHQL'
            """
            Convert Input into Search Conditions.
            """
            directive @searchBy on INPUT_FIELD_DEFINITION
            GRAPHQL;
    }

    public function manipulateArgDefinition(
        DocumentAST &$documentAST,
        InputValueDefinitionNode &$argDefinition,
        FieldDefinitionNode &$parentField,
        ObjectTypeDefinitionNode &$parentType,
    ): void {
        // Our?
        $input = $documentAST->types[ASTHelper::getUnderlyingTypeName($argDefinition)];

        if (str_ends_with($input->name->value, static::NAME)) {
            return;
        }

        // Update arg
        $type                = $this->getInputType($documentAST, $input);
        $argDefinition->type = Parser::typeReference("[{$type}!]");
    }

    protected function getInputType(
        DocumentAST $document,
        InputObjectTypeDefinitionNode $node,
    ): string {
        // Exists?
        $name = $this->getInputTypeName($node);

        if (isset($document->types[$name])) {
            return $name;
        }

        // Add dummy type to avoid infinite loop
        $document->setTypeDefinition(Parser::inputObjectTypeDefinition("
            input {$name} {
                dummy: Boolean!
            }
        "));

        // Create
        $body = [];

        foreach ($node->fields as $field) {
            /** @var \GraphQL\Language\AST\InputValueDefinitionNode $field */

            $type       = ASTHelper::getUnderlyingTypeName($field);
            $nullable   = ($field->type instanceof NonNullTypeNode);
            $typeNode   = $document->types[$type] ?? null;
            $definition = null;

            if (is_null($typeNode) && $this->isScalar($type)) {
                // TODO Is there any better way for this?
                $typeNode = $this->getScalarTypeNode($type);
            }

            if ($typeNode instanceof InputObjectTypeDefinitionNode) {
                $definition = $this->getRelationType($document, $typeNode, $nullable);
            } elseif ($typeNode instanceof ScalarTypeDefinitionNode) {
                $definition = $this->getScalarOperatorType($document, $typeNode, $nullable);
            } else {
                // empty
            }

            if ($definition) {
                $body[] = "{$field->name->value}: {$definition}\n";
            } else {
                throw new SearchByException(sprintf(
                    'Hmm... Seems `%s` not yet supported :( Please contact to developer.',
                    $typeNode ? $typeNode::class : 'null',
                ));
            }
        }

        // Add type
        $content = implode("\n", $body);

        $document->setTypeDefinition(Parser::inputObjectTypeDefinition(<<<DEF
            """
            Available conditions.
            """
            input {$name} {
                and: [{$name}!]
                or: [{$name}!]
                {$content}
            }
        DEF));

        // Return
        return $name;
    }

    protected function getScalarOperatorType(
        DocumentAST $document,
        ScalarTypeDefinitionNode $node,
        bool $nullable,
    ): string {
        // Exists?
        $name = $this->getScalarTypeName($node, $nullable);

        if (isset($document->types[$name])) {
            return $name;
        }

        // Create
        $body      = [];
        $type      = $node->name->value;
        $operators = $type;

        do {
            $operators = $this->scalars[$operators] ?? [];
        } while (!is_array($operators));

        foreach ($operators as $operator) {
            $operator     = $this->getOperator($operator);
            $operatorType = $type;

            if ($operator instanceof OperatorHasType) {
                $operatorType = $this->getScalarOperatorTypeName($node, $nullable, $operator);

                $document->setTypeDefinition(Parser::inputObjectTypeDefinition(
                    $operator->getTypeDefinition($operatorType, $type, $nullable),
                ));
            }

            $body[] = $operator->getDefinition($operatorType, $nullable);
        }

        // Body cannot be empty
        if (empty($body)) {
            throw new SearchByException(sprintf(
                'Generated scalar type is empty. Please check definition for `%s` scalar.',
                $type,
            ));
        }

        // Add null for nullable
        if ($nullable) {
            $body[] = $this->getOperator(IsNull::class)->getDefinition($type, $nullable);
            $body[] = $this->getOperator(IsNotNull::class)->getDefinition($type, $nullable);
        }

        // Add type
        $content = implode("\n", $body);

        $document->setTypeDefinition(Parser::inputObjectTypeDefinition(<<<DEF
            """
            Available operators for {$type} (only one operator allowed at a time).
            """
            input {$name} {
                {$content}
            }
        DEF));

        // Return
        return $name;
    }

    protected function getRelationType(
        DocumentAST $document,
        InputObjectTypeDefinitionNode $node,
        bool $nullable,
    ): string {
        // Exists?
        $name = $this->getRelationTypeName($node);

        if (isset($document->types[$name])) {
            return $name;
        }

        // Add dummy type to avoid infinite loop
        $document->setTypeDefinition(Parser::inputObjectTypeDefinition("
            input {$name} {
                dummy: Boolean!
            }
        "));

        // Add type
        $document->setTypeDefinition(Parser::inputObjectTypeDefinition(<<<DEF
            """
            Where Has condition.
            """
            input {$name} {
                has: Boolean = true
                where: [{$this->getInputType($document, $node)}!]
                count: {$this->getScalarOperatorType($document, $this->getScalarTypeNode('Int'), false)} = {
                    {$this->getOperator(GreaterThanOrEqual::class)->getName()}: 1
                }
            }
        DEF));

        // Return
        return $name;
    }

    protected function getScalarTypeName(ScalarTypeDefinitionNode $node, bool $nullable): string {
        return static::NAME.'Scalar'.$node->name->value.($nullable ? 'Nullable' : '');
    }

    protected function getScalarOperatorTypeName(
        ScalarTypeDefinitionNode $node,
        bool $nullable,
        Operator $operator,
    ): string {
        return $this->getScalarTypeName($node, $nullable).Str::studly($operator->getName());
    }

    protected function getInputTypeName(InputObjectTypeDefinitionNode $node): string {
        return static::NAME.'Input'.$node->name->value;
    }

    protected function getRelationTypeName(InputObjectTypeDefinitionNode $node): string {
        return static::NAME.'Relation'.$node->name->value;
    }

    protected function getScalarTypeNode(string $scalar): ScalarTypeDefinitionNode {
        return Parser::scalarTypeDefinition("scalar {$scalar}");
    }

    protected function isScalar(string $type): bool {
        return isset($this->scalars[$type]);
    }

    /**
     * @param class-string<\LastDragon_ru\LaraASP\GraphQL\SearchBy\Operator> $class
     */
    protected function getOperator(string $class): Operator {
        return $this->container->make($class);
    }
}
