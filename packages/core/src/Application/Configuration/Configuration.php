<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Application\Configuration;

use ArrayAccess;
use Illuminate\Support\Str;
use LogicException;
use Override;
use ReflectionNamedType;
use ReflectionProperty;

use function array_is_list;
use function is_a;
use function is_array;
use function is_string;
use function sprintf;
use function str_contains;

/**
 * @implements ArrayAccess<string, mixed>
 */
abstract class Configuration implements ArrayAccess {
    protected function __construct() {
        // empty
    }

    /**
     * @param array<string, mixed> $array
     */
    public static function __set_state(array $array): static {
        return new static(...$array); // @phpstan-ignore new.static (this is developer responsibility)
    }

    #[Override]
    public function offsetExists(mixed $offset): bool {
        throw new LogicException('Not supported.');
    }

    #[Override]
    public function offsetGet(mixed $offset): mixed {
        throw new LogicException('Not supported.');
    }

    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void {
        throw new LogicException('Not supported.');
    }

    #[Override]
    public function offsetUnset(mixed $offset): void {
        throw new LogicException('Not supported.');
    }

    /**
     * @deprecated %{VERSION} Array-based config is deprecated. Please migrate to object-based config.
     *
     * @param array<array-key, mixed> $array
     */
    public static function fromArray(array $array): static {
        $properties = [];

        foreach ($array as $key => $value) {
            $property              = static::fromArrayGetPropertyName($key);
            $properties[$property] = static::fromArrayGetPropertyValue($property, $value);
        }

        return static::__set_state($properties);
    }

    /**
     * @deprecated %{VERSION}
     */
    protected static function fromArrayGetPropertyName(int|string $property): string {
        if (!is_string($property)) {
            throw new LogicException(
                sprintf(
                    'The `%s::$%s` is not a valid property name.',
                    static::class,
                    $property,
                ),
            );
        }

        if (str_contains($property, '_')) {
            $property = Str::camel($property);
        }

        return $property;
    }

    /**
     * @deprecated %{VERSION}
     */
    protected static function fromArrayGetPropertyValue(string $property, mixed $value): mixed {
        if (is_array($value) && (!array_is_list($value) || $value === [])) {
            $property = new ReflectionProperty(static::class, $property);
            $type     = $property->getType();

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $name = $type->getName();

                if (is_a($name, self::class, true)) {
                    $value = $name::fromArray($value);
                }
            }
        }

        return $value;
    }
}
