<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Data;

use League\CommonMark\Node\Node;

use function is_a;
use function is_object;

/**
 * @internal
 * @template T
 */
abstract readonly class Data {
    final public function __construct(
        /**
         * @var T
         */
        protected mixed $value,
    ) {
        // empty
    }

    /**
     * @return ?T
     */
    public static function get(Node $node): mixed {
        $data  = $node->data->get(static::class, null);
        $value = is_object($data) && is_a($data, static::class, true)
            ? $data->value
            : static::default($node);

        if ($data === null && $value !== null) {
            static::set($node, $value);
        }

        return $value;
    }

    /**
     * @param T $value
     *
     * @return T
     */
    public static function set(Node $node, mixed $value): mixed {
        $node->data->set(static::class, new static($value));

        return $value;
    }

    /**
     * @return ?T
     */
    protected static function default(Node $node): mixed {
        return null;
    }
}
