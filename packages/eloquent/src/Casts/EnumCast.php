<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use LastDragon_ru\LaraASP\Eloquent\Enum;

use function gettype;
use function is_int;
use function is_null;
use function is_string;
use function sprintf;

/**
 * @implements CastsAttributes<?Enum, Enum|string|int|null>
 */
class EnumCast implements CastsAttributes {
    /**
     * @param class-string<Enum> $enum
     */
    public function __construct(
        protected string $enum,
    ) {
        // empty
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $attributes
     */
    public function get($model, string $key, $value, array $attributes): ?Enum {
        if (is_null($value) || $value instanceof $this->enum) {
            // no action required
        } elseif (is_string($value) || is_int($value)) {
            $value = $this->enum::get($value);
        } else {
            throw new InvalidArgumentException(sprintf(
                'Type `%s` cannot be converted into `%s` enum.',
                gettype($value),
                $this->enum,
            ));
        }

        return $value;
    }

    /**
     * @inheritDoc
     *
     * @param array<mixed> $attributes
     */
    public function set($model, string $key, $value, array $attributes): string|int|null {
        if (!is_null($value)) {
            $value = $this->get($model, $key, $value, $attributes)?->getValue();
        }

        return $value;
    }
}
