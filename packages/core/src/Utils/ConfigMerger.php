<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Core\Utils;

use InvalidArgumentException;
use LastDragon_ru\LaraASP\Core\Package;

use function array_key_exists;
use function array_values;
use function is_array;
use function is_null;
use function is_scalar;
use function is_string;
use function key;
use function trigger_deprecation;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '7.0.0', 'Please migrate to object-based config.');

/**
 * The merger for array-based configs.
 *
 * @deprecated 7.0.0 Please migrate to object-based config.
 */
class ConfigMerger {
    /**
     * Array that has this mark may contains any keys/values.
     */
    public const Strict = '__strict';

    /**
     * Array that has this mark will be replaced completely.
     */
    public const Replace = '__replace';

    private bool $strict = true;

    public function __construct() {
        // empty
    }

    protected function isStrict(): bool {
        return $this->strict;
    }

    protected function setStrict(bool $strict): static {
        $this->strict = $strict;

        return $this;
    }

    /**
     * Merge two or more array-based configs.
     *
     * It will respect the structure of the target array, thus you cannot add
     * any new keys, cannot replace existing scalar values by the array, and
     * vice versa. This behavior can be changed by marks.
     *
     * @see ConfigMerger::Replace
     * @see ConfigMerger::Strict
     *
     * @param array<array-key, mixed> $target
     * @param array<array-key, mixed> $configs
     *
     * @return array<array-key, mixed>
     */
    public function merge(array $target, array ...$configs): array {
        // Enable strict mode (just for case)
        $this->setStrict(true);

        // Merge
        foreach ($configs as $config) {
            $this->process($target, $config, '');
        }

        // Remove marks
        $this->cleanup($target, true);

        // Return
        return $target;
    }

    /**
     * @param array<array-key, mixed> $target
     * @param array<array-key, mixed> $config
     */
    protected function process(array &$target, array $config, string $path): void {
        // Strict?
        $isStrict = $this->isStrict();

        if ($isStrict) {
            $this->setStrict((bool) ($target[static::Strict] ?? true));
        }

        // Remove marks
        $this->cleanup($config);

        // Merge
        foreach ($config as $key => &$value) {
            // Current path
            $current = $path !== '' ? "{$path}.{$key}" : "{$key}";

            // Only scalars/nulls and arrays of them allowed
            if (!is_scalar($value) && !is_null($value) && !is_array($value)) {
                throw new InvalidArgumentException('Config may contain only scalar/null values and arrays of them.');
            }

            // Merge
            if (array_key_exists($key, $target)) {
                if (is_array($target[$key])) {
                    // In strict mode $value must be an array
                    if ($this->isStrict() && !is_array($value)) {
                        throw new InvalidArgumentException('Array cannot be replaced by scalar/null value.');
                    }

                    if (isset($target[$key][static::Replace]) && $target[$key][static::Replace] === true) {
                        $target[$key] = [static::Replace => true] + (array) $value;
                    } elseif (is_string(key($target[$key]))) {
                        $this->process($target[$key], (array) $value, $current);
                    } elseif ($target[$key] === []) {
                        $target[$key] = (array) $value;
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
                    throw new InvalidArgumentException("Unknown key `{$current}`.");
                } else {
                    $target[$key] = $value;
                }
            }
        }

        // Reset
        $this->setStrict($isStrict);
    }

    /**
     * @param array<array-key, mixed> $array
     */
    protected function cleanup(array &$array, bool $recursive = false): void {
        // Remove
        unset($array[static::Strict]);
        unset($array[static::Replace]);

        // Recursive
        if ($recursive) {
            foreach ($array as &$value) {
                if (is_array($value)) {
                    $this->cleanup($value, $recursive);
                }
            }
        }
    }
}
