<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Processor;

use Closure;
use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;

use function array_keys;
use function array_merge;
use function array_unique;
use function array_values;
use function is_object;
use function is_string;

/**
 * @template TInstance of object
 *
 * @internal
 */
class InstanceList {
    /**
     * @var array<class-string<TInstance>, ?Closure(TInstance): void>
     */
    private array $configurators = [];

    /**
     * @var array<class-string<TInstance>, TInstance>
     */
    private array $instances = [];

    /**
     * @var array<string, array<array-key, class-string<TInstance>>>
     */
    private array $map = [];

    public function __construct(
        protected readonly ContainerResolver $container,
        /**
         * @var Closure(TInstance|class-string<TInstance>):(array<array-key, string>|string|null)
         */
        protected readonly Closure $keyResolver,
    ) {
        // empty
    }

    public function isEmpty(): bool {
        return $this->map === [];
    }

    /**
     * @return list<string>
     */
    public function keys(): array {
        return array_keys($this->map);
    }

    /**
     * @return list<class-string<TInstance>>
     */
    public function classes(): array {
        $classes = [];

        foreach ($this->map as $list) {
            $classes = array_merge($classes, $list);
        }

        return array_values(array_unique($classes));
    }

    /**
     * @return list<TInstance>
     */
    public function instances(): array {
        $instances = [];

        foreach ($this->classes() as $class) {
            $instances[] = $this->resolve($class);
        }

        return $instances;
    }

    public function has(string ...$key): bool {
        $exists = false;

        foreach ($key as $k) {
            if (isset($this->map[$k])) {
                $exists = true;
                break;
            }
        }

        return $exists;
    }

    /**
     * @return list<TInstance>
     */
    public function get(string ...$key): array {
        $instances = [];

        foreach ($key as $k) {
            foreach ($this->map[$k] ?? [] as $class) {
                $instances[$class] ??= $this->resolve($class);
            }
        }

        return array_values($instances);
    }

    /**
     * @param TInstance|class-string<TInstance>                        $instance
     * @param ($instance is object ? null : ?Closure(TInstance): void) $configurator
     */
    public function add(object|string $instance, ?Closure $configurator = null): static {
        $keys = (array) ($this->keyResolver)($instance);

        foreach ($keys as $key) {
            $class                       = is_string($instance) ? $instance : $instance::class;
            $resolved                    = is_object($instance) ? $instance : null;
            $this->map[$key][]           = $class;
            $this->configurators[$class] = $configurator;

            if ($resolved !== null) {
                $this->instances[$class] = $resolved;
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
        if (!isset($this->instances[$class])) {
            $this->instances[$class] = $this->container->getInstance()->make($class);

            if (isset($this->configurators[$class])) {
                $this->configurators[$class]($this->instances[$class]);
            }
        }

        return $this->instances[$class];
    }
}
