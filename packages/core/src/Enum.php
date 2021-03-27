<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core;

use Closure;
use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use ReflectionMethod;

use function array_filter;
use function count;
use function sprintf;

abstract class Enum implements JsonSerializable {
    /**
     * @var array<class-string<static>,array<string|int,static>>
     */
    private static array $instances = [];
    /**
     * @var array<class-string<static>,bool>
     */
    private static array $values = [];

    private function __construct(
        private string|int $value,
    ) {
        // empty
    }

    // <editor-fold desc="Instance">
    // =========================================================================
    public function getValue(): string|int {
        return $this->value;
    }

    public function __toString(): string {
        return (string) $this->getValue();
    }

    public function jsonSerialize(): string|int {
        return $this->getValue();
    }
    // </editor-fold>

    // <editor-fold desc="Static">
    // =========================================================================
    public static function get(string|int $value): static {
        if (!isset(self::getValues()[$value])) {
            throw new InvalidArgumentException(sprintf(
                'Value `%s` is not valid for `%s` enum.',
                $value,
                static::class,
            ));
        }

        return self::make($value);
    }

    /**
     * @return array<string|int,static>
     */
    public static function getValues(): array {
        if (!isset(self::$values[static::class])) {
            self::lookup(static function (ReflectionMethod $method): void {
                $method->invoke(null);
            });

            self::$values[static::class] = true;
        }

        return self::$instances[static::class];
    }

    protected static function make(string|int $value): static {
        if (!isset(self::$instances[static::class][$value])) {
            self::$instances[static::class][$value] = new static($value);
        }

        return self::$instances[static::class][$value];
    }

    protected static function lookup(Closure $callback): void {
        $methods = (new ReflectionClass(static::class))->getMethods();
        $methods = array_filter($methods, static function (ReflectionMethod $method): bool {
            return $method->isStatic()
                && $method->isPublic()
                && $method->getDeclaringClass()->getName() !== self::class
                && count($method->getParameters()) === 0;
        });

        foreach ($methods as $method) {
            $callback($method);
        }
    }
    // </editor-fold>
}
