<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes\Locator;

use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Location;
use LastDragon_ru\LaraASP\Documentator\Markdown\XmlRenderer;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use Override;

use function assert;

/**
 * @internal
 */
class Renderer extends XmlRenderer {
    #[Override]
    public function getXmlTagName(Node $node): string {
        assert($node instanceof Link);

        return 'link';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getXmlAttributes(Node $node): array {
        assert($node instanceof Link);

        return [
            'url'      => $this->escape($node->getUrl()),
            'title'    => $this->escape($node->getTitle()),
            'location' => $this->location(
                Cast::toNullable(Location::class, $node->data->get(Location::class, null)),
            ),
        ];
    }
}
