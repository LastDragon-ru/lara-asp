<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\GraphQL\Builder\Traits;

use Illuminate\Container\Container;

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

    private function getHelper(object $builder): ?object {
        if (!array_key_exists($builder::class, $this->instances)) {
            $class                            = $this->getHelperClass($builder::class);
            $this->instances[$builder::class] = $class
                ? Container::getInstance()->make($class)
                : null;
        }

        return $this->instances[$builder::class];
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
}
