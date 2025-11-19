<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;
use UnitEnum;
use WeakMap;

use function array_filter;
use function array_keys;
use function array_merge;
use function array_unique;
use function is_object;
use function is_string;
use function max;
use function min;
use function usort;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * @template TInstance of object
 *
 * @internal
 */
class Instances {
    /**
     * @var array<class-string<TInstance>, int>
     */
    private array $priorities = [];

    /**
     * @var array<class-string<TInstance>, ?TInstance>
     */
    private array $resolved = [];

    /**
     * @var array<string, array<array-key, class-string<TInstance>>>
     */
    private array $tags = [];

    /**
     * @see https://github.com/php/php-src/issues/9208
     *
     * @var WeakMap<UnitEnum, array<array-key, class-string<TInstance>>>
     */
    private WeakMap $enums;

    /**
     * @var array<class-string<TInstance>, list<UnitEnum|string>>
     */
    private array $classes = [];

    /**
     * @var array<class-string<TInstance>, bool>
     */
    private array $persistent = [];

    public function __construct(
        protected readonly ContainerResolver $container,
        protected readonly SortOrder $order,
        protected readonly bool $cacheable = true,
    ) {
        $this->enums = new WeakMap();
    }

    /**
     * @return list<UnitEnum|string>
     */
    public function tags(): array {
        $tags = array_keys($this->tags);

        foreach ($this->enums as $enum => $classes) {
            $tags[] = $enum;
        }

        return $tags;
    }

    /**
     * @return list<class-string<TInstance>>
     */
    public function classes(): array {
        $classes = array_keys($this->classes);

        usort($classes, $this->compare(...));

        return $classes;
    }

    /**
     * @param TInstance|class-string<TInstance> $instance
     */
    public function is(object|string $instance): bool {
        return isset($this->classes[is_object($instance) ? $instance::class : $instance]);
    }

    public function has(UnitEnum|string|null ...$tags): bool {
        $exists = $tags === [] && ($this->tags !== [] || $this->enums->count() > 0);

        foreach ($tags as $tag) {
            if ($tag instanceof UnitEnum ? isset($this->enums[$tag]) : isset($this->tags[$tag])) {
                $exists = true;
                break;
            }
        }

        return $exists;
    }

    /**
     * @return iterable<int, TInstance>
     */
    public function get(UnitEnum|string|null ...$tags): iterable {
        $classes = $tags === [] ? $this->classes() : [];

        foreach ($tags as $tag) {
            $merge   = $tag instanceof UnitEnum ? ($this->enums[$tag] ?? []) : ($this->tags[$tag] ?? []);
            $classes = array_merge($classes, $merge);
        }

        $classes = array_unique($classes);

        usort($classes, $this->compare(...));

        foreach ($classes as $class) {
            yield $this->resolve($class);
        }

        yield from [];
    }

    /**
     * @return ?TInstance
     */
    public function first(UnitEnum|string|null ...$tags): ?object {
        $first = null;

        foreach ($this->get(...$tags) as $instance) {
            $first = $instance;
            break;
        }

        return $first;
    }

    /**
     * @param TInstance|class-string<TInstance> $instance
     * @param list<UnitEnum|string|null>        $tags
     */
    public function add(object|string $instance, array $tags = [], ?int $priority = null, bool $merge = false): static {
        if (!$merge) {
            $this->remove($instance);
        }

        $tags                     = array_filter($tags, static fn ($tag): bool => $tag !== null);
        $class                    = is_string($instance) ? $instance : $instance::class;
        $this->classes[$class]    = array_merge($this->classes[$class] ?? [], $tags);
        $this->resolved[$class]   = is_object($instance) ? $instance : null;
        $this->persistent[$class] = is_object($instance);
        $this->priorities[$class] = $priority ?? $this->priorities[$class] ?? $this->priority();

        foreach ($tags as $tag) {
            if ($tag instanceof UnitEnum) {
                $this->enums[$tag] = array_merge($this->enums[$tag] ?? [], [$class]);
            } else {
                $this->tags[$tag] = array_merge($this->tags[$tag] ?? [], [$class]);
            }
        }

        return $this;
    }

    /**
     * @param TInstance|class-string<TInstance> $instance
     */
    public function remove(object|string $instance): static {
        // Tags
        $class  = is_string($instance) ? $instance : $instance::class;
        $tags   = $this->classes[$class] ?? [];
        $filter = static function (UnitEnum|string $name) use ($class): bool {
            return $name !== $class;
        };

        foreach ($tags as $tag) {
            if ($tag instanceof UnitEnum) {
                $this->enums[$tag] = array_filter($this->enums[$tag] ?? [], $filter);

                if ($this->enums[$tag] === []) {
                    unset($this->enums[$tag]);
                }
            } else {
                $this->tags[$tag] = array_filter($this->tags[$tag] ?? [], $filter);

                if ($this->tags[$tag] === []) {
                    unset($this->tags[$tag]);
                }
            }
        }

        // Class
        unset($this->priorities[$class]);
        unset($this->persistent[$class]);
        unset($this->resolved[$class]);
        unset($this->classes[$class]);

        // Return
        return $this;
    }

    public function reset(): static {
        foreach ($this->resolved as $class => $instance) {
            if (($this->persistent[$class] ?? false) === false) {
                $this->resolved[$class] = null;
            }
        }

        return $this;
    }

    /**
     * @param class-string<TInstance> $class
     *
     * @return TInstance
     */
    protected function resolve(string $class): object {
        if (isset($this->resolved[$class])) {
            return $this->resolved[$class];
        }

        $instance = $this->container->getInstance()->make($class);

        if ($this->cacheable) {
            $this->resolved[$class] = $instance;
        }

        return $instance;
    }

    private function priority(): int {
        $priority = array_filter($this->priorities, static fn ($p) => $p < PHP_INT_MAX) + [0];
        $priority = max($priority);
        $priority = min($priority, PHP_INT_MAX - 1);
        $priority = max($priority, PHP_INT_MIN);

        return $priority + 1;
    }

    /**
     * @param class-string<TInstance> $a
     * @param class-string<TInstance> $b
     */
    private function compare(string $a, string $b): int {
        $a = $this->priorities[$a] ?? null;
        $b = $this->priorities[$b] ?? null;
        $c = $a <=> $b;

        if ($this->order === SortOrder::Desc) {
            $c = -$c;
        }

        return $c;
    }
}
