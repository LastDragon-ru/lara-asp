<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use InvalidArgumentException;

/**
 * The merger for array-based configs.
 */
class ConfigRecursiveMerger {
    private bool $strict;

    public function __construct(bool $strict = true) {
        $this->strict = $strict;
    }

    public function isStrict(): bool {
        return $this->strict;
    }

    public function setStrict(bool $strict = true): self {
        $this->strict = $strict;

        return $this;
    }

    /**
     * Merge two or more array-based configs.
     *
     * In strict mode (default) it will respect the structure of the target
     * array, thus you cannot add any new keys, cannot replace existing
     * scalar values by the array, and vice versa.
     *
     * @param array $target
     * @param array ...$configs
     *
     * @return array
     */
    public function merge(array $target, array ...$configs): array {
        foreach ($configs as $config) {
            foreach ($config as $key => $value) {
                // Only scalars/nulls and arrays of them allowed
                if (!is_scalar($value) && !is_null($value) && !is_array($value)) {
                    throw new InvalidArgumentException('Config may contain only scalar/null values and arrays of them.');
                }

                if (array_key_exists($key, $target)) {
                    if (is_array($target[$key])) {
                        // In strict mode $value must be an array
                        if ($this->isStrict() && !is_array($value)) {
                            throw new InvalidArgumentException('Array cannot be replaced by scalar/null value.');
                        }

                        if (is_string(key($target[$key]))) {
                            $target[$key] = $this->merge($target[$key], (array) $value);
                        } else {
                            $target[$key] = array_values((array) $value);
                        }
                    } else {
                        // In strict mode value cannot be replaced to array
                        if ($this->isStrict() && is_array($value)) {
                            throw new InvalidArgumentException('Scalar/null value cannot be replaced by array.');
                        } else {
                            $target[$key] = $value;
                        }
                    }
                } else {
                    // In strict mode $key must exists in $target
                    if ($this->isStrict()) {
                        throw new InvalidArgumentException('Unknown key.');
                    } else {
                        $target[$key] = $value;
                    }
                }
            }
        }

        return $target;
    }
}
