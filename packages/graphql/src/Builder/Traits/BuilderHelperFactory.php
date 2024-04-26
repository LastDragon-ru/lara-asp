<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Traits;

use LastDragon_ru\LaraASP\Core\Application\ContainerResolver;

use function array_key_exists;
use function is_a;
use function is_object;

/**
 * @internal
 */
trait BuilderHelperFactory {
    /**
     * @var array<class-string, class-string>
     */
    private array $helpers = [];

    /**
     * @var array<class-string, class-string>
     */
    private array $classes = [];

    /**
     * @var array<class-string, ?object>
     */
    private array $instances = [];

    /**
     * @param class-string $builder
     * @param class-string $helper
     */
    private function addHelper(string $builder, string $helper): static {
        $this->helpers[$builder] = $helper;

        return $this;
    }

    /**
     * @param object|class-string $builder
     */
    private function getHelper(object|string $builder): ?object {
        if (is_object($builder)) {
            $builder = $builder::class;
        }

        if (!array_key_exists($builder, $this->instances)) {
            $class                     = $this->getHelperClass($builder);
            $this->instances[$builder] = $class
                ? $this->getContainerResolver()->getInstance()->make($class)
                : null;
        }

        return $this->instances[$builder];
    }

    /**
     * @param object|class-string $builder
     *
     * @return ?class-string
     */
    private function getHelperClass(object|string $builder): ?string {
        if (is_object($builder)) {
            $builder = $builder::class;
        }

        if (!isset($this->classes[$builder])) {
            foreach ($this->helpers as $class => $sorter) {
                if (is_a($builder, $class, true)) {
                    $this->classes[$builder] = $sorter;
                    break;
                }
            }
        }

        return $this->classes[$builder];
    }

    abstract private function getContainerResolver(): ContainerResolver;
}
