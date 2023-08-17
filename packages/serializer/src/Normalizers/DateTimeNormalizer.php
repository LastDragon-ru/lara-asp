<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Normalizers;

use DateTimeInterface;
use Exception;
use Illuminate\Support\Facades\Date;
use LastDragon_ru\LaraASP\Serializer\Normalizers\Traits\WithDefaultContext;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer as SymfonyDateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

use function get_debug_type;
use function is_a;
use function is_string;
use function sprintf;

/**
 * Normalizes/Denormalizes an object implementing the {@see DateTimeInterface}
 * to/from a date string.
 *
 * It is designed specially for Laravel and respects how we are usually work
 * with Dates. Here are few differences/improvements from
 * {@see SymfonyDateTimeNormalizer}:
 *
 * - It supports denormalization for any object that implements {@see DateTimeInterface}.
 * - It uses {@see Date::createFromFormat()} for denormalization to make sure
 *   that the proper `DateTime` instance will be used.
 * - If {@see Date::createFromFormat()} failed and {@see self::ContextFallback}
 *   enabled the {@see Date::make()} will be used. It may be useful, for example,
 *   if {@see self::ContextFormat} was changed.
 *
 * @see DateTimeNormalizerContextBuilder
 * @see SymfonyDateTimeNormalizer
 */
class DateTimeNormalizer implements NormalizerInterface, DenormalizerInterface {
    use WithDefaultContext;

    final public const ContextFormat          = self::class.'@format';
    final public const ContextFormatDefault   = DateTimeInterface::RFC3339_EXTENDED;
    final public const ContextFallback        = self::class.'@fallback';
    final public const ContextFallbackDefault = false;

    /**
     * @param array<string, mixed> $defaultContext
     */
    public function __construct(
        array $defaultContext = [],
    ) {
        $this->setDefaultContext($defaultContext);
    }

    /**
     * @return array<class-string, bool>
     */
    public function getSupportedTypes(?string $format): array {
        return [
            DateTimeInterface::class => self::class === static::class,
        ];
    }

    /**
     * @param array<array-key, mixed> $context
     */
    public function normalize(mixed $object, string $format = null, array $context = []): string {
        if (!($object instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$object` expected to be a `%s`, `%s` given.',
                    DateTimeInterface::class,
                    get_debug_type($object),
                ),
            );
        }

        return $object->format(
            $this->getContextOption($context, self::ContextFormat, self::ContextFormatDefault),
        );
    }

    public function supportsNormalization(mixed $data, string $format = null): bool {
        return $data instanceof DateTimeInterface;
    }

    /**
     * @param array<array-key, mixed> $context
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): mixed {
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

        if (!is_a($type, DateTimeInterface::class, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The `$type` expected to be a `%s`, `%s` given.',
                    DateTimeInterface::class,
                    get_debug_type($data),
                ),
            );
        }

        // Facade is called to make sure that the expected DateTime class will be used.
        $fallback = $this->getContextOption($context, self::ContextFallback, self::ContextFallbackDefault);
        $format   = $this->getContextOption($context, self::ContextFormat, self::ContextFormatDefault);
        $result   = null;
        $error    = null;

        try {
            $result = Date::createFromFormat($format, $data);
        } catch (Exception $exception) {
            if ($fallback) {
                $error = $exception;
            } else {
                throw $exception;
            }
        }

        if (!($result instanceof DateTimeInterface) && $fallback) {
            try {
                $result = Date::make($data);
            } catch (Exception $exception) {
                throw $error ?? $exception;
            }
        }

        if (!($result instanceof DateTimeInterface)) {
            throw new UnexpectedValueException(
                sprintf(
                    'The `%s` cannot be parsed to `DateTime`.',
                    $data,
                ),
            );
        }

        return $result;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool {
        return is_a($type, DateTimeInterface::class, true);
    }
}
