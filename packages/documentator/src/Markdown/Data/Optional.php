<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Data;

use LastDragon_ru\LaraASP\Documentator\Markdown\Exceptions\DataMissed;
use League\CommonMark\Node\Node;

/**
 * @template T
 *
 * @internal
 */
readonly class Optional {
    final public function __construct(
        /**
         * @var class-string<Data<T>>
         */
        protected string $data,
    ) {
        // empty
    }

    /**
     * @return ?T
     */
    public function get(Node $node): mixed {
        try {
            return $this->data::get($node);
        } catch (DataMissed) {
            return null;
        }
    }
}
