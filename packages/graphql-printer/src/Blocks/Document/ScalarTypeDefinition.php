<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\ScalarTypeDefinitionNode;
use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IDType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\StringType;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\TypeDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Testing\Package\GraphQLAstNode;
use LastDragon_ru\GraphQLPrinter\Testing\Package\GraphQLDefinition;
use Override;

/**
 * @internal
 *
 * @extends DefinitionBlock<ScalarTypeDefinitionNode|ScalarType>
 */
#[GraphQLAstNode(ScalarTypeDefinitionNode::class)]
#[GraphQLDefinition(BooleanType::class)]
#[GraphQLDefinition(CustomScalarType::class)]
#[GraphQLDefinition(FloatType::class)]
#[GraphQLDefinition(IDType::class)]
#[GraphQLDefinition(IntType::class)]
#[GraphQLDefinition(StringType::class)]
class ScalarTypeDefinition extends DefinitionBlock implements TypeDefinitionBlock {
    public function __construct(
        Context $context,
        ScalarTypeDefinitionNode|ScalarType $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return 'scalar';
    }
}
