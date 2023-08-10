<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer;

use DateTimeInterface;
use Illuminate\Container\Container;
use LastDragon_ru\LaraASP\Serializer\Contracts\Serializer as SerializerContract;
use LastDragon_ru\LaraASP\Serializer\Normalizers\SerializableNormalizer;
use LastDragon_ru\LaraASP\Serializer\Normalizers\SerializableNormalizerContextBuilder;
use Symfony\Component\Serializer\Context\Encoder\JsonEncoderContextBuilder;
use Symfony\Component\Serializer\Context\Normalizer\DateTimeNormalizerContextBuilder;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

use function config;

use const JSON_BIGINT_AS_STRING;
use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_LINE_TERMINATORS;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class Factory {
    /**
     * @param array<class-string<EncoderInterface|DecoderInterface>, array<string, mixed>|null>         $encoders
     * @param array<class-string<NormalizerInterface|DenormalizerInterface>, array<string, mixed>|null> $normalizers
     * @param array<string, mixed>                                                                      $context
     */
    public function create(
        array $encoders = [],
        array $normalizers = [],
        array $context = [],
        string $default = null,
    ): SerializerContract {
        $default     = $default ?? $this->getConfigFormat() ?? JsonEncoder::FORMAT;
        $context     = $context + $this->getConfigContext();
        $encoders    = $this->getEncoders($encoders, $context);
        $normalizers = $this->getNormalizers($normalizers, $context);
        $serializer  = new Serializer(
            new SymfonySerializer($normalizers, $encoders),
            $default,
            $context,
        );

        return $serializer;
    }

    protected function getConfigFormat(): ?string {
        /** @var ?string $format */
        $format = config(Package::Name.'.default');

        return $format;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getConfigContext(): array {
        /** @var array<string, mixed> $context */
        $context = (array) config(Package::Name.'.context');

        return $context;
    }

    /**
     * @param array<class-string<EncoderInterface|DecoderInterface>, array<string, mixed>|null> $encoders
     * @param array<string, mixed>                                                              $context
     *
     * @return list<EncoderInterface|DecoderInterface>
     */
    protected function getEncoders(array $encoders, array &$context): array {
        $encoders  = $encoders + $this->getConfigEncoders() + $this->getDefaultEncoders();
        $container = Container::getInstance();
        $instances = [];

        foreach ($encoders as $encoder => $options) {
            $instances[] = $container->make($encoder);
            $context     = ((array) $options) + $context;
        }

        return $instances;
    }

    /**
     * @return array<class-string<EncoderInterface|DecoderInterface>, array<string, mixed>|null>
     */
    protected function getConfigEncoders(): array {
        /** @var array<class-string<EncoderInterface|DecoderInterface>, array<string, mixed>|null> $encoders */
        $encoders = (array) config(Package::Name.'.encoders');

        return $encoders;
    }

    /**
     * @return array<class-string<EncoderInterface|DecoderInterface>, array<string, mixed>|null>
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
     * @return list<NormalizerInterface|DenormalizerInterface>
     */
    protected function getNormalizers(array $normalizers, array &$context): array {
        $normalizers = $normalizers + $this->getConfigNormalizers() + $this->getDefaultNormalizers();
        $container   = Container::getInstance();
        $instances   = [];

        foreach ($normalizers as $normalizer => $options) {
            $instances[] = $container->make($normalizer);
            $context     = ((array) $options) + $context;
        }

        return $instances;
    }

    /**
     * @return array<class-string<NormalizerInterface|DenormalizerInterface>, array<string, mixed>|null>
     */
    protected function getConfigNormalizers(): array {
        /** @var array<class-string<NormalizerInterface|DenormalizerInterface>, array<string, mixed>|null> $normalizers */
        $normalizers = (array) config(Package::Name.'.normalizers');

        return $normalizers;
    }

    /**
     * @return array<class-string<NormalizerInterface|DenormalizerInterface>, array<string, mixed>|null>
     */
    protected function getDefaultNormalizers(): array {
        return [
            ArrayDenormalizer::class      => null,
            DateTimeNormalizer::class     => (new DateTimeNormalizerContextBuilder())
                ->withFormat(DateTimeInterface::RFC3339_EXTENDED)
                ->toArray(),
            DateTimeZoneNormalizer::class => null,
            DateIntervalNormalizer::class => null,
            SerializableNormalizer::class => (new SerializableNormalizerContextBuilder())
                ->withAllowExtraAttributes(false)
                ->withDisableTypeEnforcement(false)
                ->withSkipNullValues(false)
                ->withSkipUninitializedValues(true)
                ->withPreserveEmptyObjects(true)
                ->toArray(),
        ];
    }
}
