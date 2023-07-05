<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DocumentNode;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Factory;
use LastDragon_ru\LaraASP\GraphQLPrinter\Blocks\Types\DefinitionList;
use LastDragon_ru\LaraASP\GraphQLPrinter\Misc\Context;
use LastDragon_ru\LaraASP\GraphQLPrinter\Testing\Package\GraphQLAstNode;

/**
 * @internal
 */
#[GraphQLAstNode(DocumentNode::class)]
class Document extends Block {
    public function __construct(
        Context $context,
        private DocumentNode $document,
    ) {
        parent::__construct($context);
    }

    protected function getDocument(): DocumentNode {
        return $this->document;
    }

    protected function content(int $level, int $used): string {
        $definitions = new DefinitionList($this->getContext());
        $document    = $this->getDocument();
        $context     = $this->getContext();

        foreach ($document->definitions as $definition) {
            $definitions[] = Factory::create($context, $definition);
        }

        return $this->addUsed($definitions)->serialize($level, $used);
    }
}
