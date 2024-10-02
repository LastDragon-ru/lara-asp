<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Casts;

use Illuminate\Container\Container;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use LastDragon_ru\LaraASP\Serializer\Exceptions\FailedToCast;
use LastDragon_ru\LaraASP\Serializer\Package;
use Override;

use function is_string;
use function trigger_deprecation;

// phpcs:disable PSR1.Files.SideEffects

trigger_deprecation(Package::Name, '6.4.0', 'Please use `%s` instead.', Serialized::class);

/**
 * @deprecated 6.4.0 Please use {@see Serialized} instead.
 *
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
     * @param class-string $class
     */
    public static function using(string $class, ?string $format = null): string {
        return static::class.':'.$class.($format !== null && $format !== '' ? ",{$format}" : '');
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function get(Model $model, string $key, mixed $value, array $attributes): ?object {
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
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed {
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
