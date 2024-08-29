<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Nodes;

use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location;
use League\CommonMark\Node\Node;

/**
 * @internal
 */
interface XmlRenderer {
    public function getXmlTagName(Node $node): string;

    /**
     * @return array<string, Location|scalar|null>
     */
    public function getXmlAttributes(Node $node): array;
}
