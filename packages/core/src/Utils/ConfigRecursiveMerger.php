<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use InvalidArgumentException;
use LogicException;

use function in_array;

/**
 * The merger for array-based configs.
 */
class ConfigRecursiveMerger {
    private bool $strict;
    /**
     * @var string[]
     */
    private array $unprotected;

    /**
     * @param string[] $unprotected
     */
    public function __construct(bool $strict = true, array $unprotected = []) {
        $this->strict      = $strict;
        $this->unprotected = $unprotected;

        if (!$this->isStrict() && $this->getUnprotected()) {
            throw new LogicException('Setting the `$unprotected` paths has no effect in non-strict mode.');
        }
    }

    public function isStrict(): bool {
        return $this->strict;
    }

    /**
     * @return string[]
     */
    public function getUnprotected(): array {
        return $this->unprotected;
    }

    /**
     * Merge two or more array-based configs.
     *
     * In strict mode (default) it will respect the structure of the target
     * array, thus you cannot add any new keys, cannot replace existing
     * scalar values by the array, and vice versa.
     */
    public function merge(array $target, array ...$configs): array {
        foreach ($configs as $config) {
            $target = $this->process($target, $config, '');
        }

        return $target;
    }

    protected function process(array $target, array $config, string $path): array {
        foreach ($config as $key => $value) {
            // Current path
            $current = $path ? "{$path}.{$key}" : $key;

            // Only scalars/nulls and arrays of them allowed
            if (!is_scalar($value) && !is_null($value) && !is_array($value)) {
                throw new InvalidArgumentException('Config may contain only scalar/null values and arrays of them.');
            }

            if (array_key_exists($key, $target)) {
                if (is_array($target[$key])) {
                    // In strict mode $value must be an array
                    if ($this->isProtected($path) && !is_array($value)) {
                        throw new InvalidArgumentException('Array cannot be replaced by scalar/null value.');
                    }

                    if (is_string(key($target[$key]))) {
                        $target[$key] = $this->process($target[$key], (array) $value, $current);
                    } else {
                        $target[$key] = array_values((array) $value);
                    }
                } else {
                    // In strict mode value cannot be replaced to array
                    if ($this->isProtected($path) && is_array($value)) {
                        throw new InvalidArgumentException('Scalar/null value cannot be replaced by array.');
                    } else {
                        $target[$key] = $value;
                    }
                }
            } else {
                // In strict mode $key must exists in $target
                if ($this->isProtected($path)) {
                    throw new InvalidArgumentException("Unknown key `{$current}`.");
                } else {
                    $target[$key] = $value;
                }
            }
        }

        return $target;
    }

    public function isProtected(string $path): bool {
        return $this->isStrict()
            && (empty($this->getUnprotected()) || !in_array($path, $this->getUnprotected(), true));
    }
}
