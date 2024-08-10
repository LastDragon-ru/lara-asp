<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Xml\XmlNodeRendererInterface;
use Override;
use Stringable;

use function assert;

/**
 * @internal
 */
class Renderer implements NodeRendererInterface, XmlNodeRendererInterface {
    #[Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable|string|null {
        return null;
    }

    #[Override]
    public function getXmlTagName(Node $node): string {
        assert($node instanceof Block);

        return 'reference';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getXmlAttributes(Node $node): array {
        assert($node instanceof Block);

        return [
            'label'       => $node->getLabel(),
            'destination' => $node->getDestination(),
            'title'       => $node->getTitle(),
        ];
    }
}
