<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Serializer\Normalizers;

use LastDragon_ru\LaraASP\Serializer\Contracts\Serializable;
use LastDragon_ru\LaraASP\Serializer\Metadata\MetadataFactory;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use function class_exists;

/**
 * Special serializer for {@see Serializable}.
 *
 * Only public properties will be serialized. Accessors/mutators/magic/etc
 * doesn't supported. If you need it, please consider using one of Symfony's
 * normalizer like {@see ObjectNormalizer}.
 *
 * @see SerializableNormalizerContextBuilder
 * @see ObjectNormalizer
 */
class SerializableNormalizer extends AbstractObjectNormalizer {
    /**
     * @param array<string, mixed> $defaultContext
     */
    public function __construct(
        MetadataFactory $metadata,
        array $defaultContext = [],
    ) {
        if (!class_exists(PropertyAccess::class)) {
            /**
             * The class is required for {@see AbstractObjectNormalizer}. We
             * need to add the package to our 'composer.json' since
             * `symfony/serializer` doesn't require it directly. But it
             * will lead to "unused package error" during CI checks. So the
             * condition needs only for CI :)
             */
        }

        parent::__construct(
            classMetadataFactory : $metadata,
            propertyTypeExtractor: $metadata,
            defaultContext       : $defaultContext,
        );
    }

    /**
     * @return array<class-string, bool>
     */
    public function getSupportedTypes(?string $format): array {
        return [
            Serializable::class => self::class === static::class,
        ];
    }

    /**
     * @param array<array-key, mixed> $context
     *
     * @return array<array-key, string>
     */
    protected function extractAttributes(object $object, string $format = null, array $context = []): array {
        /** This method will be called if {@see self::ALLOW_EXTRA_ATTRIBUTES} is `true`. */
        $attributes = [];

        if ($this->classMetadataFactory) {
            foreach ($this->classMetadataFactory->getMetadataFor($object)->getAttributesMetadata() as $metadata) {
                $attributes[] = $metadata->getName();
            }
        }

        return $attributes;
    }

    /**
     * @param array<array-key, mixed> $context
     */
    protected function getAttributeValue(
        object $object,
        string $attribute,
        string $format = null,
        array $context = [],
    ): mixed {
        return $object->{$attribute};
    }

    /**
     * @param array<array-key, mixed> $context
     */
    protected function setAttributeValue(
        object $object,
        string $attribute,
        mixed $value,
        string $format = null,
        array $context = [],
    ): void {
        $object->{$attribute} = $value;
    }
}
