<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use ReflectionClass;

use function str_starts_with;

class ClassUtils {
    /**
     * Get class public constants values.
     *
     * @throws \ReflectionException
     *
     * @return array<mixed>
     */
    public static function getConstants(object|string $class, string $prefix = null, bool $recursive = true): array {
        $values    = [];
        $constants = (new ReflectionClass($class))->getReflectionConstants();

        foreach ($constants as $constant) {
            if (!$constant->isPublic()) {
                continue;
            }

            if ($prefix && !str_starts_with($constant->getName(), $prefix)) {
                continue;
            }

            if (!$recursive && $constant->getDeclaringClass()->name !== $class) {
                continue;
            }

            $values[] = $constant->getValue();
        }

        return $values;
    }
}
