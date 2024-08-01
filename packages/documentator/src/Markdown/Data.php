<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Markdown;

use LastDragon_ru\LaraASP\Documentator\Markdown\Data\Value;
use League\CommonMark\Node\Node;

use function is_a;
use function is_object;

/**
 * @internal
 */
class Data {
    /**
     * @template T
     *
     * @param class-string<Value<T>> $data
     *
     * @return ?T
     */
    public static function get(Node $node, string $data): mixed {
        $value = $node->data->get($data, null);
        $value = is_object($value) && is_a($value, $data, true)
            ? $value->get()
            : null;

        return $value;
    }

    /**
     * @template T
     *
     * @param Value<T> $value
     *
     * @return T
     */
    public static function set(Node $node, mixed $value): mixed {
        $node->data->set($value::class, $value);

        return $value->get();
    }

    /**
     * @param class-string<Value<*>> $data
     */
    public static function remove(Node $node, string $data): void {
        $node->data->remove($data);
    }
}
