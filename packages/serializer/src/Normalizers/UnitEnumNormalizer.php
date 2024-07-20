<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Normalizers;

use BackedEnum;
use LastDragon_ru\LaraASP\Core\Utils\Cast;
use LastDragon_ru\LaraASP\Serializer\Normalizers\Traits\WithDefaultContext;
use Override;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use UnitEnum;

use function get_debug_type;
use function is_a;
use function is_string;
use function sprintf;

/**
 * Normalizes/Denormalizes an {@see UnitEnum} enumeration to/from a case name.
 */
class UnitEnumNormalizer implements NormalizerInterface, DenormalizerInterface {
    use WithDefaultContext;

    final public const ContextAllowInvalidValues        = self::class.'@allowInvalidValues';
    final public const ContextAllowInvalidValuesDefault = false;

    /**
     * @var array<class-string<UnitEnum>, array<string, UnitEnum>>
     */
    private array $cases = [];

    /**
     * @param array<string, mixed> $defaultContext
     */
    public function __construct(array $defaultContext = []) {
        $this->setDefaultContext($defaultContext);
    }

    /**
     * @return array<class-string, bool>
     */
    public function getSupportedTypes(?string $format): array {
        return [
            UnitEnum::class => self::class === static::class,
        ];
    }

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    public function normalize(mixed $object, ?string $format = null, array $context = []): string {
        if (!$this->supportsNormalization($object, $format)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$object` expected to be a `%s`, `%s` given.',
                    UnitEnum::class,
                    get_debug_type($object),
                ),
            );
        }

        return $object->name;
    }

    /**
     * @param array<array-key, mixed> $context
     *
     * @phpstan-assert-if-true UnitEnum $data
     */
    #[Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool {
        return $data instanceof UnitEnum && !($data instanceof BackedEnum);
    }

    /**
     * @param array<array-key, mixed> $context
     */
    #[Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed {
        // Just for the case
        if (!is_string($data)) {
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
        $result       = $this->getCases($type)[$data] ?? null;
        $allowInvalid = $this->getContextOptionFormatAllowInvalidValues($context);

        if ($result === null && !$allowInvalid) {
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
     * @param array<array-key, mixed> $context
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
        return is_a($type, UnitEnum::class, true) && !is_a($type, BackedEnum::class, true);
    }

    /**
     * @param class-string<UnitEnum> $enum
     *
     * @return array<string, UnitEnum>
     */
    private function getCases(string $enum): array {
        // Cached?
        if (isset($this->cases[$enum])) {
            return $this->cases[$enum];
        }

        // Get
        $this->cases[$enum] = [];

        foreach ($enum::cases() as $case) {
            $this->cases[$enum][$case->name] = $case;
        }

        return $this->cases[$enum];
    }

    /**
     * @param array<array-key, mixed> $context
     */
    protected function getContextOptionFormatAllowInvalidValues(array $context): bool {
        return Cast::toBool(
            $this->getContextOption($context, self::ContextAllowInvalidValues, self::ContextAllowInvalidValuesDefault),
        );
    }
}
