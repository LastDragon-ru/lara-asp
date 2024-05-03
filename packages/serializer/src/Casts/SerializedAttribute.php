<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Casts;

use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer;
use LastDragon_ru\LaraASP\Serializer\Exceptions\FailedToCast;

use function is_a;
use function is_string;

/**
 * @internal
 *
 * @extends Attribute<?object, ?object>
 */
class SerializedAttribute extends Attribute {
    public function __construct(
        protected readonly Serializer $serializer,
        /**
         * @var class-string
         */
        protected readonly string $class,
        protected readonly string $format = 'json',
        /**
         * @var array<string, mixed>
         */
        protected readonly array $context = [],
    ) {
        parent::__construct(
            get: fn (mixed $value) => $this->deserialize($value),
            set: fn (?object $value) => $this->serialize($value),
        );
    }

    protected function serialize(?object $value): ?string {
        // Null?
        if ($value === null) {
            return null;
        }

        // Expected?
        if (!is_a($value, $this->class, true)) {
            throw new FailedToCast($this->class, $value);
        }

        // Process
        try {
            return $this->serializer->serialize($value, $this->format, $this->context);
        } catch (Exception $exception) {
            throw new FailedToCast($this->class, $value, $exception);
        }
    }

    protected function deserialize(mixed $value): ?object {
        // Null?
        if ($value === null) {
            return null;
        }

        // Expected?
        if (!is_string($value)) {
            throw new FailedToCast($this->class, $value);
        }

        // Process
        try {
            return $this->serializer->deserialize($this->class, $value, $this->format, $this->context);
        } catch (Exception $exception) {
            throw new FailedToCast($this->class, $value, $exception);
        }
    }
}
