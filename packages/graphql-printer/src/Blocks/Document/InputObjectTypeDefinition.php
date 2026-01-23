<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\Parser;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\InputObjectType;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\InputObjectDefinitionBlock;
use LastDragon_ru\GraphQLPrinter\Feature;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\GraphQLAstNode;
use LastDragon_ru\GraphQLPrinter\Package\GraphQLDefinition;
use Override;

/**
 * @internal
 *
 * @extends InputObjectDefinitionBlock<InputObjectTypeDefinitionNode|InputObjectType>
 */
#[GraphQLAstNode(InputObjectTypeDefinitionNode::class)]
#[GraphQLDefinition(InputObjectType::class)]
class InputObjectTypeDefinition extends InputObjectDefinitionBlock {
    public function __construct(
        Context $context,
        InputObjectTypeDefinitionNode|InputObjectType $definition,
    ) {
        parent::__construct($context, $definition);
    }

    #[Override]
    protected function prefix(): ?string {
        return 'input';
    }

    #[Override]
    protected function getDefinitionDirectives(): NodeList {
        // Is `@oneOf` supported?
        $definition = $this->getDefinition();
        $directives = parent::getDefinitionDirectives();

        if (
            !Feature::OneOfDirective->available()
            || !($definition instanceof InputObjectType)
            || !isset($definition->isOneOf)
            || !$definition->isOneOf
        ) {
            return $directives;
        }

        // Add
        $oneOfName      = Directive::ONE_OF_NAME;
        $oneOfDirective = Parser::directive("@{$oneOfName}");

        foreach ($directives as $key => $directive) {
            if ($directive->name->value === $oneOfName) {
                $directives[$key] = $oneOfDirective;
                $oneOfDirective   = null;
                break;
            }
        }

        if ($oneOfDirective !== null) {
            $directives[] = $oneOfDirective;
        }

        // Return
        return $directives;
    }
}
