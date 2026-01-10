<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Data;

use League\CommonMark\Node\Node;
use Override;

use function iterator_to_array;

/**
 * @internal
 * @extends Data<array<int, string>>
 */
readonly class Lines extends Data {
    #[Override]
    protected static function default(Node $node): mixed {
        return iterator_to_array(Input::get($node)->getLines());
    }
}
