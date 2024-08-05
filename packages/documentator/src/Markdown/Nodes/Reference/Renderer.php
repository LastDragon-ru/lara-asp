<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Reference;

use LastDragon_ru\LaraASP\Documentator\Markdown\XmlRenderer;
use League\CommonMark\Node\Node;
use Override;

use function assert;

/**
 * @internal
 */
class Renderer extends XmlRenderer {
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
            'label'       => $this->escape($node->getLabel()),
            'destination' => $this->escape($node->getDestination()),
            'title'       => $this->escape($node->getTitle()),
            'location'    => $this->location($node),
        ];
    }
}
