<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Documentator\Utils;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_search;
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
abstract class Instances {
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
     * @var array<class-string<TInstance>, array<array-key, string>>
     */
    private array $classes = [];

    public function __construct(
        protected readonly ContainerResolver $container,
        protected readonly SortOrder $order,
    ) {
        // empty
    }

    /**
     * @return list<string>
     */
    public function getTags(): array {
        return array_keys($this->tags);
    }

    /**
     * @return list<class-string<TInstance>>
     */
    public function getClasses(): array {
        $classes = array_keys($this->resolved);

        usort($classes, $this->compare(...));

        return $classes;
    }

    /**
     * @param TInstance|class-string<TInstance> $instance
     */
    public function is(object|string $instance): bool {
        return array_key_exists(
            is_object($instance) ? $instance::class : $instance,
            $this->resolved,
        );
    }

    public function has(?string ...$tags): bool {
        $exists = $tags === [] && $this->tags !== [];

        foreach ($tags as $tag) {
            if (isset($this->tags[$tag])) {
                $exists = true;
                break;
            }
        }

        return $exists;
    }

    /**
     * @return iterable<int, TInstance>
     */
    public function get(?string ...$tags): iterable {
        $classes = $tags === [] ? $this->getClasses() : [];

        foreach ($tags as $tag) {
            $classes = array_merge($classes, $this->tags[$tag] ?? []);
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
    public function first(?string ...$tags): ?object {
        $first = null;

        foreach ($this->get(...$tags) as $instance) {
            $first = $instance;
            break;
        }

        return $first;
    }

    /**
     * @param TInstance|class-string<TInstance> $instance
     * @param list<?string>                     $tags
     */
    public function add(object|string $instance, array $tags = [], ?int $priority = null): static {
        $tags                     = array_filter($tags, static fn (?string $tag): bool => $tag !== null);
        $class                    = is_string($instance) ? $instance : $instance::class;
        $this->classes[$class]    = array_unique(array_merge($this->classes[$class] ?? [], $tags));
        $this->resolved[$class]   = is_object($instance) ? $instance : null;
        $this->priorities[$class] = $priority ?? $this->priorities[$class] ?? $this->getPriority();

        foreach ($tags as $tag) {
            $this->tags[$tag] = array_unique(array_merge($this->tags[$tag] ?? [], [$class]));
        }

        return $this;
    }

    /**
     * @param TInstance|class-string<TInstance> $instance
     */
    public function remove(object|string $instance): static {
        $class = is_string($instance) ? $instance : $instance::class;
        $tags  = $this->classes[$class] ?? [];

        unset($this->priorities[$class]);
        unset($this->resolved[$class]);

        foreach ($tags as $tag) {
            $index = array_search($class, $this->tags[$tag] ?? [], true);

            if ($index !== false) {
                unset($this->tags[$tag][$index]);
            }

            if (($this->tags[$tag] ?? []) === []) {
                unset($this->tags[$tag]);
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
        $this->resolved[$class] ??= $this->container->getInstance()->make($class);

        return $this->resolved[$class];
    }

    private function getPriority(): int {
        $priority = max($this->priorities + [0]);
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
