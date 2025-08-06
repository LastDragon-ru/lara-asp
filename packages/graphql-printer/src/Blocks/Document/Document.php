<?php declare(strict_types = 1);

namespace LastDragon_ru\GraphQLPrinter\Blocks\Document;

use GraphQL\Language\AST\DocumentNode;
use LastDragon_ru\GraphQLPrinter\Blocks\Block;
use LastDragon_ru\GraphQLPrinter\Blocks\Types\DefinitionList;
use LastDragon_ru\GraphQLPrinter\Misc\Collector;
use LastDragon_ru\GraphQLPrinter\Misc\Context;
use LastDragon_ru\GraphQLPrinter\Package\GraphQLAstNode;
use Override;

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

    #[Override]
    protected function content(Collector $collector, int $level, int $used): string {
        $context     = $this->getContext();
        $document    = $this->getDocument();
        $definitions = new DefinitionList($context, $document->definitions, static fn() => null);

        return $definitions->serialize($collector, $level, $used);
    }
}
