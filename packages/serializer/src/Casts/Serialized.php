<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Casts;

use Illuminate\Database\Eloquent\Casts\Attribute;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;

class Serialized {
    public function __construct(
        protected readonly Serializer $serializer,
    ) {
        // empty
    }

    /**
     * @template T of object
     *
     * @param class-string<T>      $class
     * @param array<string, mixed> $context
     *
     * @return Attribute<?T, ?T>
     */
    public function attribute(string $class, string $format = 'json', array $context = []): Attribute {
        // @phpstan-ignore-next-line method.unresolvableReturnType I've no idea how to make it work...
        return new SerializedAttribute($this->serializer, $class, $format, $context);
    }
}
