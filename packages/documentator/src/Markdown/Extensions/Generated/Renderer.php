<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated;

use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\EndMarkerLocation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\Generated\Data\StartMarkerLocation;
use LastDragon_ru\LaraASP\Documentator\Markdown\Extensions\XmlRenderer;
use League\CommonMark\Node\Node;
use Override;

use function assert;

/**
 * @internal
 */
class Renderer implements XmlRenderer {
    #[Override]
    public function getXmlTagName(Node $node): string {
        assert($node instanceof Block);

        return 'generated';
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getXmlAttributes(Node $node): array {
        assert($node instanceof Block);

        return [
            'id'                  => $node->id,
            'startMarkerLocation' => StartMarkerLocation::optional()->get($node),
            'endMarkerLocation'   => EndMarkerLocation::optional()->get($node),
        ];
    }
}
