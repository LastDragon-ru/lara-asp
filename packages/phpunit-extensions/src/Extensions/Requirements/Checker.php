<?php declare(strict_types = 1);

namespace LastDragon_ru\PhpUnit\Extensions\Requirements;

use LastDragon_ru\PhpUnit\Extensions\Requirements\Contracts\Requirement;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;

use function array_merge;
use function array_unique;
use function array_values;

/**
 * @internal
 */
class Checker {
    /**
     * @var array<class-string, list<string>>
     */
    private array $classes = [];

    /**
     * @var array<string, list<string>>
     */
    private array $methods = [];

    public function __construct() {
        // empty
    }

    /**
     * @param class-string $class
     * @param list<string> $failed
     */
    public function isSatisfied(string $class, ?string $method = null, array &$failed = []): bool {
        $failed = array_merge(
            $this->getClassFailedRequirements($class),
            $method !== null
                ? $this->getMethodFailedRequirements($class, $method)
                : [],
        );

        return $failed === [];
    }

    /**
     * @param class-string $class
     *
     * @return list<string>
     */
    protected function getClassFailedRequirements(string $class): array {
        return $this->classes[$class] ??= $this->getFailedRequirements(new ReflectionClass($class));
    }

    /**
     * @param class-string $class
     *
     * @return list<string>
     */
    protected function getMethodFailedRequirements(string $class, string $method): array {
        return $this->methods["{$class}::{$method}()"] ??= $this->getFailedRequirements(
            new ReflectionMethod($class, $method),
        );
    }

    /**
     * @param ReflectionClass<object>|ReflectionMethod $object
     *
     * @return list<string>
     */
    protected function getFailedRequirements(ReflectionClass|ReflectionMethod $object): array {
        $failed       = [];
        $requirements = $object->getAttributes(Requirement::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($requirements as $requirement) {
            $instance = $requirement->newInstance();
            $reason   = $instance->isSatisfied() ? false : (string) $instance;

            if ($reason !== false) {
                $failed[] = $reason;
                break;
            }
        }

        return array_values(array_unique($failed));
    }
}
