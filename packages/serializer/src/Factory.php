<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer;

use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer as SerializerContract;
use LastDragon_ru\LaraASP\Serializer\Normalizers\DateTimeNormalizer;
use LastDragon_ru\LaraASP\Serializer\Normalizers\DateTimeNormalizerContextBuilder;
use LastDragon_ru\LaraASP\Serializer\Normalizers\SerializableNormalizer;
use LastDragon_ru\LaraASP\Serializer\Normalizers\SerializableNormalizerContextBuilder;
use LastDragon_ru\LaraASP\Serializer\Normalizers\UnitEnumNormalizer;
use LastDragon_ru\LaraASP\Serializer\Normalizers\UnitEnumNormalizerContextBuilder;
use Symfony\Component\Serializer\Context\Encoder\JsonEncoderContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\BackedEnumNormalizerContextBuilder;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function config;

use const JSON_BIGINT_AS_STRING;
use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_LINE_TERMINATORS;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class Factory {
    /**
     * @param array<class-string<EncoderInterface|DecoderInterface>, array<string, mixed>>              $encoders
     * @param array<class-string<NormalizerInterface|DenormalizerInterface>, array<string, mixed>|null> $normalizers
     *      The `null` value can be used to remove the built-in normalizer.
     * @param array<string, mixed>                                                                      $context
     */
    public function create(
        array $encoders = [],
        array $normalizers = [],
        array $context = [],
        string $format = null,
        ?string $config = Package::Name,
    ): SerializerContract {
        $format      = $format ?? $this->getConfigFormat($config) ?? JsonEncoder::FORMAT;
        $context     = $context + $this->getConfigContext($config);
        $encoders    = $this->getEncoders($encoders, $context, $config);
        $normalizers = $this->getNormalizers($normalizers, $context, $config);
        $serializer  = $this->make($encoders, $normalizers, $context, $format);

        return $serializer;
    }

    /**
     * @private for tests only
     *
     * @param list<class-string<EncoderInterface|DecoderInterface>>         $encoders
     * @param list<class-string<NormalizerInterface|DenormalizerInterface>> $normalizers
     * @param array<string, mixed>                                          $context
     */
    protected function make(
        array $encoders,
        array $normalizers,
        array $context,
        string $format,
    ): SerializerContract {
        $container           = Container::getInstance();
        $encoderInstances    = [];
        $normalizerInstances = [];

        foreach ($encoders as $class) {
            $encoderInstances[] = $container->make($class);
        }

        foreach ($normalizers as $class) {
            $normalizerInstances[] = $container->make($class);
        }

        return new Serializer(
            new SymfonySerializer($normalizerInstances, $encoderInstances),
            $format,
            $context,
        );
    }

    protected function getConfigFormat(?string $config): ?string {
        /** @var ?string $format */
        $format = $config ? config("{$config}.default") : null;

        return $format;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getConfigContext(?string $config): array {
        /** @var array<string, mixed> $context */
        $context = $config ? (array) config("{$config}.context") : [];

        return $context;
    }

    /**
     * @param array<class-string<EncoderInterface|DecoderInterface>, array<string, mixed>> $encoders
     * @param array<string, mixed>                                                         $context
     *
     * @return list<class-string<EncoderInterface|DecoderInterface>>
     */
    protected function getEncoders(array $encoders, array &$context, ?string $config): array {
        $groups = [$encoders, $this->getConfigEncoders($config), $this->getDefaultEncoders()];
        $list   = [];

        foreach ($groups as $group) {
            foreach ($group as $encoder => $options) {
                if (!isset($list[$encoder])) {
                    $list[$encoder] = true;
                }

                $context += $options;
            }
        }

        return array_keys($list);
    }

    /**
     * @return array<class-string<EncoderInterface|DecoderInterface>, array<string, mixed>>
     */
    protected function getConfigEncoders(?string $config): array {
        /** @var array<class-string<EncoderInterface|DecoderInterface>, array<string, mixed>> $encoders */
        $encoders = $config ? (array) config("{$config}.encoders") : [];

        return $encoders;
    }

    /**
     * @return array<class-string<EncoderInterface|DecoderInterface>, array<string, mixed>>
     */
    protected function getDefaultEncoders(): array {
        return [
            JsonEncoder::class => (new JsonEncoderContextBuilder())
                ->withEncodeOptions(
                    JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_LINE_TERMINATORS
                    | JSON_BIGINT_AS_STRING
                    | JSON_PRESERVE_ZERO_FRACTION
                    | JSON_THROW_ON_ERROR,
                )
                ->withDecodeOptions(
                    JSON_THROW_ON_ERROR,
                )
                ->toArray(),
        ];
    }

    /**
     * @param array<class-string<NormalizerInterface|DenormalizerInterface>, array<string, mixed>|null> $normalizers
     * @param array<string, mixed>                                                                      $context
     *
     * @return list<class-string<NormalizerInterface|DenormalizerInterface>>
     */
    protected function getNormalizers(array $normalizers, array &$context, ?string $config): array {
        $groups = [$normalizers, $this->getConfigNormalizers($config), $this->getDefaultNormalizers()];
        $list   = [];

        foreach ($groups as $group) {
            foreach ($group as $normalizer => $options) {
                if (!array_key_exists($normalizer, $list)) {
                    $list[$normalizer] = true;
                }

                if ($options === null) {
                    $list[$normalizer] = false;
                } elseif ($list[$normalizer]) {
                    $context += $options;
                } else {
                    // ignore
                }
            }
        }

        return array_keys(array_filter($list));
    }

    /**
     * @return array<class-string<NormalizerInterface|DenormalizerInterface>, array<string, mixed>|null>
     */
    protected function getConfigNormalizers(?string $config): array {
        /** @var array<class-string<NormalizerInterface|DenormalizerInterface>, array<string, mixed>|null> $normalizers */
        $normalizers = $config ? (array) config("{$config}.normalizers") : $config;

        return $normalizers;
    }

    /**
     * @return array<class-string<NormalizerInterface|DenormalizerInterface>, array<string, mixed>>
     */
    protected function getDefaultNormalizers(): array {
        return [
            ArrayDenormalizer::class      => [],
            DateTimeNormalizer::class     => (new DateTimeNormalizerContextBuilder())
                ->withFormat(DateTimeNormalizer::ContextFormatDefault)
                ->toArray(),
            DateTimeZoneNormalizer::class => [],
            DateIntervalNormalizer::class => [],
            UnitEnumNormalizer::class     => (new UnitEnumNormalizerContextBuilder())
                ->withAllowInvalidValues(false)
                ->toArray(),
            BackedEnumNormalizer::class   => (new BackedEnumNormalizerContextBuilder())
                ->withAllowInvalidValues(false)
                ->toArray(),
            SerializableNormalizer::class => (new SerializableNormalizerContextBuilder())
                ->withDisableTypeEnforcement(false)
                ->withSkipNullValues(false)
                ->withSkipUninitializedValues(true)
                ->withPreserveEmptyObjects(true)
                ->withAllowExtraAttributes(false)
                ->toArray(),
        ];
    }
}
