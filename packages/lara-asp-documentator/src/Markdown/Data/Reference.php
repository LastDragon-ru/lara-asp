<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Data;

use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Reference\ReferenceInterface;

/**
 * @internal
 */
readonly class Reference {
    public static function get(AbstractWebResource $node): ?ReferenceInterface {
        $reference = $node->data->get('reference', null);
        $reference = $reference instanceof ReferenceInterface ? $reference : null;

        return $reference;
    }
}
