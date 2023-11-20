<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Casts;

use Illuminate\Container\Container;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use LastDragon_ru\LaraASP\Serializer\Exceptions\FailedToCast;
use Override;

use function is_string;

// todo(laravel): [update] Update methods signatures after remove v9.x support.

/**
 * @template TGet of object
 * @template TSet of object
 *
 * @implements CastsAttributes<TGet, TGet|TSet>
 */
class AsSerializable implements CastsAttributes {
    public function __construct(
        /**
         * @var class-string<TGet>
         */
        protected readonly string $class,
        protected readonly string $format = 'json',
        /**
         * @var array<string, mixed>
         */
        protected readonly array $context = [],
    ) {
        // empty
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function get(mixed $model, string $key, mixed $value, array $attributes): ?object {
        if ($value === null || $value instanceof $this->class) {
            // no action
        } elseif (is_string($value)) {
            $value = Container::getInstance()->make(Serializer::class)->deserialize(
                $this->class,
                $value,
                $this->format,
                $this->context,
            );
        } else {
            throw new FailedToCast($this->class, $value);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function set(mixed $model, string $key, mixed $value, array $attributes): mixed {
        if ($value !== null) {
            $value = Container::getInstance()->make(Serializer::class)->serialize(
                $value,
                $this->format,
                $this->context,
            );
        }

        return [
            $key => $value,
        ];
    }
}
