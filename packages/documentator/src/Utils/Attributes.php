<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use ReflectionAttribute;
use ReflectionClass;

/**
 * @internal
 */
class Attributes {
    /**
     * @template T of object
     *
     * @param object|class-string $class
     * @param class-string<T>     $attribute
     *
     * @return iterable<mixed, T>
     */
    public static function get(object|string $class, string $attribute): iterable {
        $class  = $class instanceof ReflectionClass ? $class : new ReflectionClass($class);
        $parent = $class->getParentClass();

        if ($parent !== false) {
            yield from static::get($parent, $attribute);
        }

        foreach ($class->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF) as $reflection) {
            yield $reflection->newInstance();
        }
    }
}
