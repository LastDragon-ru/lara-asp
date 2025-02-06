<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;

use function array_keys;
use function array_merge;
use function array_search;
use function array_unique;
use function array_values;
use function end;
use function is_object;
use function is_string;
use function max;
use function min;
use function reset;
use function usort;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

/**
 * @template TInstance of object
 *
 * @internal
 */
abstract class Instances {
    /**
     * @var array<class-string<TInstance>, int>
     */
    private array $priorities = [];

    /**
     * @var array<class-string<TInstance>, TInstance>
     */
    private array $resolved = [];

    /**
     * @var array<string, array<array-key, class-string<TInstance>>>
     */
    private array $map = [];

    public function __construct(
        protected readonly ContainerResolver $container,
    ) {
        // empty
    }

    public function isEmpty(): bool {
        return $this->map === [];
    }

    /**
     * @return list<string>
     */
    public function getKeys(): array {
        return array_keys($this->map);
    }

    /**
     * @return list<class-string<TInstance>>
     */
    public function getClasses(): array {
        $classes = [];

        foreach ($this->map as $list) {
            $classes = array_merge($classes, $list);
        }

        $classes = array_values(array_unique($classes));

        usort($classes, $this->compare(...));

        return $classes;
    }

    /**
     * @return list<TInstance>
     */
    public function getInstances(): array {
        $instances = [];

        foreach ($this->getClasses() as $class) {
            $instances[] = $this->resolve($class);
        }

        return $instances;
    }

    public function has(?string ...$key): bool {
        $exists = false;

        foreach ($key as $k) {
            if ($k !== null && isset($this->map[$k]) && $this->map[$k] !== []) {
                $exists = true;
                break;
            }
        }

        return $exists;
    }

    /**
     * @return list<TInstance>
     */
    public function get(?string ...$key): array {
        $instances = [];

        foreach ($key as $k) {
            if ($k !== null && isset($this->map[$k])) {
                foreach ($this->map[$k] as $class) {
                    $instances[$class] ??= $this->resolve($class);
                }
            }
        }

        $instances = array_values($instances);

        usort($instances, $this->compare(...));

        return $instances;
    }

    /**
     * @return ?TInstance
     */
    public function first(?string ...$key): ?object {
        $instances = $this->get(...$key);
        $instance  = reset($instances);
        $instance  = $instance !== false ? $instance : null;

        return $instance;
    }

    /**
     * @return ?TInstance
     */
    public function last(?string ...$key): ?object {
        $instances = $this->get(...$key);
        $instance  = end($instances);
        $instance  = $instance !== false ? $instance : null;

        return $instance;
    }

    /**
     * @param TInstance|class-string<TInstance> $instance
     */
    public function add(object|string $instance, ?int $priority = null): static {
        // Remove
        $this->remove($instance);

        // Add
        $keys                     = $this->getInstanceKeys($instance);
        $class                    = is_string($instance) ? $instance : $instance::class;
        $this->priorities[$class] = $priority ?? $this->getInstancePriority($instance);

        if (is_object($instance)) {
            $this->resolved[$class] = $instance;
        }

        foreach ($keys as $key) {
            $this->map[$key][] = $class;
        }

        return $this;
    }

    /**
     * @param TInstance|class-string<TInstance> $instance
     */
    public function remove(object|string $instance): static {
        $class = is_string($instance) ? $instance : $instance::class;
        $keys  = $this->getInstanceKeys($instance);

        unset($this->priorities[$class]);
        unset($this->resolved[$class]);

        foreach ($keys as $key) {
            $index = array_search($class, $this->map[$key] ?? [], true);

            if ($index !== false) {
                unset($this->map[$key][$index]);
            }

            if (($this->map[$key] ?? []) === []) {
                unset($this->map[$key]);
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
        if (!isset($this->resolved[$class])) {
            $this->resolved[$class] = $this->container->getInstance()->make($class);
        }

        return $this->resolved[$class];
    }

    /**
     * @param TInstance|class-string<TInstance> $instance
     *
     * @return list<string>
     */
    abstract protected function getInstanceKeys(object|string $instance): array;

    /**
     * @param TInstance|class-string<TInstance> $instance
     */
    protected function getInstancePriority(object|string $instance): int {
        $priority = max($this->priorities + [0]);
        $priority = min($priority, PHP_INT_MAX - 1);
        $priority = max($priority, PHP_INT_MIN + 1);

        return $priority + 1;
    }

    /**
     * @param TInstance|class-string<TInstance> $a
     * @param TInstance|class-string<TInstance> $b
     */
    private function compare(object|string $a, object|string $b): int {
        $a = $this->priorities[is_object($a) ? $a::class : $a] ?? null;
        $b = $this->priorities[is_object($b) ? $b::class : $b] ?? null;
        $c = $a <=> $b;

        return $c;
    }
}
