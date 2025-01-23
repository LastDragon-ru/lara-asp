<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown\Data;

use LastDragon_ru\LaraASP\Documentator\Markdown\Exceptions\DataMissed;
use LastDragon_ru\LaraASP\Documentator\Package;
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
        public mixed $value,
    ) {
        // empty
    }

    /**
     * @return T
     */
    public static function get(Node $node): mixed {
        // Cached?
        $data = $node->data->get(Package::Name.'.'.static::class, null);

        if (is_object($data) && is_a($data, static::class, true)) {
            return $data->value;
        }

        // Default?
        $value = static::default($node);

        if ($value === null && is_a(static::class, Nullable::class, true)) {
            return static::set($node, $value);
        }

        if ($value === null) {
            throw new DataMissed($node, static::class);
        }

        return static::set($node, $value);
    }

    /**
     * @return Optional<T>
     */
    public static function optional(): Optional {
        return new Optional(static::class);
    }

    /**
     * @param T $value
     *
     * @return T
     */
    public static function set(Node $node, mixed $value): mixed {
        $node->data->set(Package::Name.'.'.static::class, new static($value));

        return $value;
    }

    /**
     * @return ?T
     */
    protected static function default(Node $node): mixed {
        return null;
    }
}
