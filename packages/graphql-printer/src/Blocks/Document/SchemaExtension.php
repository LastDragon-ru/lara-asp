<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\SchemaExtensionNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\ExtensionDefinitionBlock;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

use function mb_strlen;

/**
 * @internal
 *
 * @extends DefinitionBlock<SchemaExtensionNode>
 */
#[GraphQLAstNode(SchemaExtensionNode::class)]
class SchemaExtension extends DefinitionBlock implements ExtensionDefinitionBlock {
    public function __construct(
        Context $context,
        int $level,
        int $used,
        SchemaExtensionNode $definition,
    ) {
        parent::__construct($context, $level, $used, $definition);
    }

    protected function type(): string|null {
        return 'extend schema';
    }

    protected function body(int $used): Block|string|null {
        return null;
    }

    protected function fields(int $used): Block|string|null {
        $definition = $this->getDefinition();
        $space      = $this->space();
        $fields     = new RootOperationTypesDefinition(
            $this->getContext(),
            $this->getLevel(),
            $used + mb_strlen($space),
        );

        foreach ($definition->operationTypes as $operation) {
            $fields[] = new RootOperationTypeDefinition(
                $this->getContext(),
                $this->getLevel() + 1,
                $this->getUsed(),
                $operation->operation,
                $operation->type,
            );
        }

        return $this->addUsed($fields);
    }
}
