<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Normalizers;

use BackedEnum;
use Override;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use UnitEnum;

use function array_find;
use function get_debug_type;
use function is_a;
use function is_int;
use function is_string;
use function sprintf;

/**
 * Normalizes/Denormalizes an {@see UnitEnum} enumeration to/from a case name or a value (if backed).
 */
class EnumNormalizer implements NormalizerInterface, DenormalizerInterface {
    public function __construct() {
        // empty
    }

    /**
     * @return array<class-string, bool>
     */
    #[Override]
    public function getSupportedTypes(?string $format): array {
        return [
            UnitEnum::class => self::class === static::class,
        ];
    }

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): string|int {
        if (!$this->supportsNormalization($object, $format)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$object` expected to be a `%s`, `%s` given.',
                    UnitEnum::class,
                    get_debug_type($object),
                ),
            );
        }

        return $object instanceof BackedEnum
            ? $object->value
            : $object->name;
    }

    /**
     * @param array<array-key, mixed>   $context
     *
     * @phpstan-assert-if-true UnitEnum $data
     */
    #[Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool {
        return $data instanceof UnitEnum;
    }

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed {
        // Just for the case
        if (!is_string($data) && !is_int($data)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$data` expected to be a `%s`, `%s` given.',
                    'string',
                    get_debug_type($data),
                ),
            );
        }

        if (!$this->supportsDenormalization($data, $type, $format)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$type` expected to be a `%s`, `%s` given.',
                    UnitEnum::class,
                    $type,
                ),
            );
        }

        // Search
        $result = !is_a($type, BackedEnum::class, true)
            ? array_find($type::cases(), static fn ($case) => $case->name === $data)
            : $type::tryFrom($data);

        if ($result === null) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                sprintf(
                    'The data must belong to a enumeration of type `%s`.',
                    $type,
                ),
                $data,
                [$type],
                isset($context['deserialization_path']) && is_string($context['deserialization_path'])
                    ? $context['deserialization_path']
                    : null,
                true,
            );
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed>                 $context
     *
     * @phpstan-assert-if-true class-string<UnitEnum> $type
     */
    #[Override]
    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        return is_a($type, UnitEnum::class, true);
    }
}
