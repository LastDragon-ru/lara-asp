<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Data;

use LastDragon_ru\LaraASP\Documentator\Markdown\Location\Location as LocationContract;
use LastDragon_ru\LaraASP\Documentator\Markdown\Utils;
use League\CommonMark\Node\Block\AbstractBlock;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Node\Node;
use Override;

/**
 * @internal
 * @extends Data<LocationContract>
 */
readonly class Location extends Data {
    #[Override]
    protected static function default(Node $node): mixed {
        $location = null;

        if ($node instanceof AbstractBlock) {
            $start   = $node->getStartLine();
            $end     = $node->getEndLine();
            $offset  = Offset::get($node) ?? 0;
            $length  = Length::get($node);
            $padding = Utils::getPadding($node, null, null);

            if ($padding === null && $node->parent() instanceof Document) {
                $padding = 0;
            }

            if ($start !== null && $end !== null && $padding !== null) {
                $location = new LocationContract($start, $end, $offset, $length, $padding);
            }
        }

        return $location;
    }
}
